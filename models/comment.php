<?php
/**
 * В данном классе реализован интерфейс итератора,
 * для перебора результата в том порядке,
 * в котором должны выводиться комментарии
 * Таблица
 * CREATE TABLE comments (
 *   id int AUTO_INCREMENT PRIMARY KEY,
 *   comment text,
 *   parent_id int REFERENCES comments (id)
 * );
 */

class Comment implements Iterator {
    private $comments = []; //здесь хранятся выбранные данные
    private $roots = []; //переменная итератора, корневые элементы
    private $index; //переменная итератора, указатель
    private $valid = true; //переменная итератора, конец перебора
    public $depth = 1; //переменная итератора, уровень вложенности

    private function normalization($arr) //метод возвращает массив данных, ключами которого являются id
    {
        $res = [];
        foreach ($arr as $v) {
            $res[$v['id']] = $v;
            unset($res[$v['id']]['id']);
        }
        return $res;
    }

    public function fetchAll() //метод выбирает все данные из базы
    {
        global $db;
        $res = pg_fetch_all(pg_query($db, 'SELECT * FROM comments'));
        if ($res === false) return false;
        $this->comments = $this->normalization($res);
        return true;
    }

    public function fetchDescendants (int $id) //метод выбирает потомков узла
    {
        global $db;
        $res = pg_fetch_all(pg_query_params($db,
            'WITH RECURSIVE res AS (
                SELECT
                    id, comment, parent_id
                FROM comments
                WHERE id=$1
                UNION
                SELECT
                    comments.id, comments.comment, comments.parent_id
                FROM comments JOIN res ON res.id = comments.parent_id
            )
            SELECT * FROM res;',
            [$id]
        ));
        if ($res === false) return false;
        $this->comments = $this->normalization($res);
        return true;
    }

    public function fetchAncestors (int $id) //метод выбирает предков узла
    {
        global $db;
        $res = pg_fetch_all(pg_query_params($db,
            'WITH RECURSIVE res AS (
                SELECT
                    id, comment, parent_id
                FROM comments
                WHERE id=$1
                UNION
                SELECT
                    comments.id, comments.comment, comments.parent_id
                FROM comments JOIN res ON comments.id = res.parent_id
            )
            SELECT * FROM res;',
            [$id]
        ));
        if ($res === false) return false;
        $this->comments = $this->normalization($res);
        return true;
    }

    public function rewind()
    {
        if (empty($this->comments)) {
            $this->valid = false;
            return;
        }
        foreach ($this->comments as &$v) {
            foreach ($this->comments as $k1 => $v1) {
                if ((int) $v['parent_id'] !== (int) $k1) continue;
                continue 2;
            }
            $v['parent_id'] = null;
        }
        $this->roots = array_filter($this->comments, function($comment) {
            if ($comment['parent_id'] === null) return true;
            return false;
        });
        reset($this->roots);
        $this->index = key($this->roots);
        return;
    }

    public function current()
    {
        return $this->comments[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $res = null;
        $index = $this->index;
        $this->depth++;
        while ($res == null) {
            foreach ($this->comments as $k => $v) {
                if ($index != $v['parent_id']) continue;
                $res = $k;
                break;
            }
            if ($res != null) break;
            $tmp = $index;
            $index = $this->comments[$index]['parent_id'];
            $this->comments[$tmp] = null;
            $this->depth--;
            if ($index != null) continue;
            if (next($this->roots) == false) $this->valid = false;
            $res = key($this->roots);
        }
        $this->index = $res;
        return;
    }

    public function valid()
    {
        return $this->valid;
    }
}
