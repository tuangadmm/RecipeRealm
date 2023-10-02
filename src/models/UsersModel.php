<?php

namespace Src\models;

use Exception;
use Src\models\database\Context;
use Src\utils\PasswordHash;

class UsersModel
{
    private Context $db;

    public function __construct(Context $db)
    {
        $this->db = $db;
    }

    /**
     * Check if given username and password match any record on database
     * @param string $username
     * @param string $password
     * @return array
     */
    public function loginWithUsername(string $username, string $password): array
    {
        $res = [];
        try{
            $conn = $this->db->getConnection();
            $sql = "select * from users where username = ? ";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->execute();

            $res = $stmt->fetch(2);
            if($res){
                if(!PasswordHash::verify($password, $res['password'])){
                    $res['passwordErr'] = 'Password is incorrect';
                }else{
                    $res['success'] = 'Login success';
                }
            }else{
                $res['usernameErr'] = 'Username does not exists';
            }
        }catch (Exception $e){
            echo 'loginWithUsername failed. ' . $e->getMessage();
        } finally {
            $this->db->closeConnection();
        }
        return $res;
    }

    /**
     * Check if given email and password match any record on database
     * @param string $email
     * @param string $password
     * @return array
     */
    public function loginWithEmail(string $email, string $password): array
    {
        $res = [];
        try{
            $conn = $this->db->getConnection();
            $sql = "select * from users where email = ? ";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $email);
            $stmt->execute();

            $res = $stmt->fetch(2);
            if($res){
                if(!PasswordHash::verify($password, $res['password'])){
                    $res['passwordErr'] = 'Password is incorrect';
                }else{
                    $res['success'] = 'Login success';
                }
            }else{
                $res['emailErr'] = 'Email does not exists';
            }
        }catch (Exception $e){
            echo 'loginWithEmail failed. ' . $e->getMessage();
        } finally {
            $this->db->closeConnection();
        }
        return $res;
    }

    /**
     * Insert new user to database
     * @param string $username
     * @param string $password
     * @param string $confirmPassword
     * @param string $email
     * @return array
     */
    public function register(string $username, string $password, string $confirmPassword, string $email): array
    {
        $res = [];
        $ok = 1;
        try{
            $conn = $this->db->getConnection();

            if($password !== $confirmPassword){
                $res['passwordErr'] = 'Passwords do not matched';
                $ok = 0;
            }

            if($this->usernameExist($username)){
                $res['usernameErr'] = "Username '$username' is already taken";
                $ok = 0;
            }

            if($this->emailExist($email)){
                $res['emailErr'] = "Email '$email' is already taken";
                $ok = 0;
            }

            if($ok == 1){
                $hashedPass = PasswordHash::hash($password);
                $sql = 'insert into users( username, password, email) value (?, ?, ?)';
                $stmt = $conn->prepare($sql);

                if($stmt->execute([$username, $hashedPass, $email])){
                    $res['success'] = "Register success";
                }
            }

        }catch (Exception $e){
            echo 'register failed. ' . $e->getMessage();
        } finally {
            $this->db->closeConnection();
        }
        return $res;

    }

    /**
     * Get all user detail
     * @param int $id
     * @return ?array
     */
    public function getUserById(int $id): ?array
    {
        try{
            $conn = $this->db->getConnection();
            $sql = "select * from users 
                        join recipes on users.user_id = recipes.user_id 
                        join favorites f on recipes.recipe_id = f.recipe_id and f.user_id = users.user_id 
                        join recipe_realm.likes l on recipes.recipe_id = l.recipe_id and users.user_id = l.user_id 
                        where users.user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            $res = $stmt->fetch(2);

            return $res ?: null;

        }catch (Exception $e){
            echo 'getUserById failed. ' . $e->getMessage();
            return null;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Return user_id gy given username, return false if not found
     * @param string $username
     * @return int|bool
     */
    public function getUserIdByUsername(string $username): int|bool
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select user_id from users where username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username]);

            $res = $stmt->fetch(2);

            return $res ? $res[0] : false;
        }catch (Exception $e){
            echo 'getUserIdByUsername failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Check if username is already exist
     * @param string $username
     * @return bool
     */
    private function usernameExist(string $username): bool
    {
        try{
            $conn = $this->db->getConnection();
            $sql = "select * from users where username = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $username);
            $stmt->execute();

            return $stmt->fetch(2);

        }catch (Exception $e){
            echo 'usernameExist failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Check if email is already exist ( null value excluded )
     * @param string $email
     * @return bool
     */
    private function emailExist(string $email): bool
    {
        try{
            $conn = $this->db->getConnection();
            $sql = "select * from users where email = ? and email is not null";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(1, $email);
            $stmt->execute();

            return $stmt->fetch(2);
        }catch (Exception $e){
            echo 'emailExist failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }
}