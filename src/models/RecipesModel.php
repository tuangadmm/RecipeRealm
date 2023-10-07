<?php

namespace Src\models;

use Src\models\database\Context;

class RecipesModel
{
    private Context $db;

    public function __construct(Context $db)
    {
        $this->db = $db;
    }


    public function createRecipe(
        string $username,
        string $thumbnail,
        string $title,
        string $description,
        string $instruction,
        array $categories,
        array $images
    ): bool
    {
        $userId = (new UsersModel($this->db))->getUserIdByUsername($username);
        try{
            $conn = $this->db->getConnection();

            if($userId){

            }
            return true;
        }catch (\Exception $e){
            echo 'createRecipe failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Get all recipes paged
     * @param int $page
     * @param int $itemsPerPage
     * @return array|null
     */
    public function getAllRecipesPaged(int $page, int $itemsPerPage): ?array
    {
        try{
            $offset = $page <= 0 ? 0 : $page - 1;

            $conn = $this->db->getConnection();
            $sql = "select recipe_id, thumbnail, title, description, created_date from recipes limit ? offset ? ";
            $stmt = $conn->prepare($sql);

            $stmt->execute([$itemsPerPage, $offset]);
            $res = $stmt->fetchAll(2);

            return $res ?: null;
        }catch (\Exception $e){
            echo 'getAllRecipesPaged failed. ' . $e->getMessage();
        } finally {
            $this->db->closeConnection();
        }
        return null;
    }

    /**
     * Get all recipes posted by user
     * @param string $username
     * @return array|null
     */
    public function getRecipesListByUsername(string $username): ?array
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select recipe_id, thumbnail, title, description, recipes.created_date as rep_created
                        from recipes 
                        join users on recipes.user_id = users.user_id 
                        where users.username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username]);

            $res = $stmt->fetchAll(2);
            return $res ?: null;

        }catch (\Exception $e){
            echo 'getRecipeListByUsername failed . ' . $e->getMessage();
            return null;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Get recipe details include: recipe and its creator
     * @param int $recipeId
     * @return mixed|null
     */
    public function getDetails(int $recipeId): mixed
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select recipe_id, username, thumbnail, title, description, instruction, recipes.created_date as rep_created from recipes 
                        join users on recipes.user_id = users.user_id 
                        where recipe_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$recipeId]);
            $res = $stmt->fetch(2);

            return $res ?: null;
        }catch (\Exception $e){
            echo 'getDetail failed. ' . $e->getMessage();
            return null;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Get total likes for a recipe
     * @param int $recipeId
     * @return int
     */
    public function getLikeCount(int $recipeId): int
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select count(*) as count from likes where recipe_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$recipeId]);
            $res = $stmt->fetch(2);

            return $res ? $res['count'] : 0;
        }catch (\Exception $e){
            echo 'getLikeCount failed. ' . $e->getMessage();
            return 0;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Get total favorites for a recipe
     * @param int $recipeId
     * @return int
     */
    public function getFavCount(int $recipeId): int
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select count(*) as count from favorites where recipe_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$recipeId]);
            $res = $stmt->fetch(2);

            return $res ? $res['count'] : 0;
        }catch (\Exception $e){
            echo 'getFavCount failed. ' . $e->getMessage();
            return 0;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Get categories of a recipe
     * @param int $recipeId
     * @return ?array
     */
    public function getRecipeCategories(int $recipeId): ?array
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select cat_name from categories, rel_recipe_cat  
                        where categories.cat_id = rel_recipe_cat.cat_id 
                        and recipe_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$recipeId]);
            $res = $stmt->fetchAll(2);

            return $res ?: null;
        }catch (\Exception $e){
            echo 'getRecipeCategories failed. ' . $e->getMessage();
            return null;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Get comments of a recipe
     * @param int $recipeId
     * @return ?array
     */
    public function getComments(int $recipeId): ?array
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select username, email, comment_content, comments.created_date as com_created from comments, users   
                        where comments.user_id = users.user_id  
                        and recipe_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$recipeId]);
            $res = $stmt->fetchAll(2);

            return $res ?: null;
        }catch (\Exception $e){
            echo 'getComments failed. ' . $e->getMessage();
            return null;
        } finally {
            $this->db->closeConnection();
        }
    }

    private function uploadImages(int $recipeId, array $images)
    {
        
    }


}