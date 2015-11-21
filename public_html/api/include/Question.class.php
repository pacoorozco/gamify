<?php

require_once dirname(__FILE__) . '/Database.class.php';

class Question {

    private $database;

    function __construct()
    {
        $this->database = new Database();
    }

    public function getAllForUserId($user_id) {
        // $this->database->query('SELECT q.* FROM questions q, members_questions mq WHERE q.id <> mq.id_question AND mq.id_member = :user_id');
        $this->database->query('SELECT * FROM questions');
        $this->database->bind(':user_id', $user_id);
        $questions = $this->database->fetchAll();
        return $questions;
    }


}
