<?php

namespace MyApp;

class Poll {
    private $_db;

    public function __construct() {
        $this->_connectDB();
        $this->_createToken();
    }

    private function _createToken() {
        if (!isset($_SESSION['token'])) // セッション情報は、$_SESSION['キー'] = 値;
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(16)); // bin2hex関数 指定した文字列のバイナリーデータを16進数に変換する。
    }

    private function _validateToken() {
        if (
            !isset($_SESSION['token']) || 
            !isset($_POST['token']) || 
            $_SESSION['token'] !== $_POST['token']
        ) {
            throw new \Exception('invalid token!');
        }
    }

    public function post(){
        try{
            $this->_validateToken();
            $this->_validateAnswer();
            $this->_save();

            // redirect to result.php
            header('Location: http://' . $_SERVER['HTTP_POST'] . '/result.php');
        } catch (\Exception $e) {
            // set error
            $_SESSION['err'] = $e->getMessage();
            // redirect to index.php
            header('Location: http://' . $_SERVER['HTTP_HOST']);
        }
        exit;
    }

    public function getResults() {
        $data = array_fill(0, 3, 0); // 指定した要素の数、値で配列を初期化する（「はじめの配列キー」, 「要素の数」, 「要素の値」）

        $sql = "select answer, count(id) as c from answers group by answer";
        foreach ($this->_db->query($sql) as $row){      // query()
            $data[$row['answer']] = (int)$row['c'];
        }
        return $data;
    }
    
    public function getError() {
        $err = null;
        if (isset($_SESSION['err'])) {
          $err = $_SESSION['err'];
          unset($_SESSION['err']);
        }
        return $err;
    }

    private function _validateAnswer() {
        // var_dump($_POST);
        // exit;
        if (
          !isset($_POST['answer']) ||
          !in_array($_POST['answer'], [0, 1, 2])   // in_array 特定の値が配列に含まれているか調べる　（調べたい値, 対象となる配列, ）
        ) {
          throw new \Exception('invalid answer!');
        }
    }

    private function _save() {
        $sql = "insert into answers
                (answer, created, remote_addr, user_agent, answer_date)
                values (:answer, now(), :remote_addr, :user_agent, now())";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue(':answer', (int)$_POST['answer'], \PDO::PARAM_INT);
        $stmt->bindValue(':remote_addr', $_SERVER['REMOTE_ADDR'], \PDO::PARAM_STR);
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'], \PDO::PARAM_STR);
        
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \Exception('No more vote for today!');
        }
        // exit;
    }

    private function _connectDB() {
        try {
          $this->_db = new \PDO(DSN, DB_USERNAME, DB_PASSWORD);
          $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
          throw new \Exception('Failed to connect DB');
        }
}




