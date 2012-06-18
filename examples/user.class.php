<?php

/**
 * User service class
 */
class User {
    public function get($userId){
        $user = new stdClass();
        $user->id = $userId;
        $user->firstName = 'foo';
        $user->lastName = 'bar';

        return $user;
    }

    private function dontTryMe(){
        return "I don't think it matters";
    }
}
