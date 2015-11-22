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

    public function getQuestionForUserId($question_uuid, $user_id) {
        // $this->database->query('SELECT q.* FROM questions q, members_questions mq WHERE q.id <> mq.id_question AND mq.id_member = :user_id');
        $this->database->query('SELECT * FROM questions WHERE uuid = :question_uuid');
        $this->database->bind(':question_uuid', $question_uuid);
        $question = $this->database->fetchOne();
        return $question;
    }

    public function getChoicesForQuestion($question_id) {
        $this->database->query('SELECT * FROM questions_choices WHERE question_id = :question_id');
        $this->database->bind(':question_id', $question_id);
        $choices = $this->database->fetchAll();
        return $choices;
    }

}
