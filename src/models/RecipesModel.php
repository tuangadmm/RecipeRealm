<?php

namespace Src\models;

use Src\models\database\Context;
use Src\utils\FileUpload;

class RecipesModel
{
    private Context $db;
    private UsersModel $userModel;

    public function __construct(Context $db, UsersModel $userModel)
    {
        $this->db = $db;
        $this->userModel = $userModel;
    }

    /**
     * Insert new recipe
     * @param string $username
     * @param string $thumbnail
     * @param string $title
     * @param string $description
     * @param string $instruction
     * @param array $categories
     * @param array $images
     * @return bool
     */
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
        $userId = $this->userModel->getUserIdByUsername($username);
        try{
            $conn = $this->db->getConnection();

            if($userId){
                //upload images to server
                $fileUpload = new FileUpload();
                if(!$fileUpload->upload($images)) return false;

                //insert information to database
                $conn->beginTransaction();

                //insert recipe information
                $sql = "insert into recipes(user_id, thumbnail, title, description, instruction) values (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$userId, $thumbnail, $title, $description, $instruction]);

                //get last inserted recipe_id
                $repId = $conn->lastInsertId();

                //insert categories
                $this->uploadCategories($repId, $categories);

                //insert recipe images
                if($this->uploadImages($repId, $fileUpload->getUploaded())){
                    $conn->commit();
                    return true;
                }
                $conn->rollBack();
            }
            return false;
        }catch (\Exception $e){
            echo 'createRecipe failed. ' . $e->getMessage();
            $conn->rollBack();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Delete recipe from database
     * @param string $username
     * @param int $recipeId
     * @return bool
     */
    public function deleteRecipe(string $username, int $recipeId): bool
    {
        $userId = $this->userModel->getUserIdByUsername($username);
        if($userId && $this->existsByUserIdAndRecipeId($userId, $recipeId)){
            try{
                $conn = $this->db->getConnection();

                //get list of image names
                $sql = "select image_url from recipe_images where recipe_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$recipeId]);
                $toBeDeleted = $stmt->fetchAll(2);

                //delete information from database
                $conn->beginTransaction();
                $queries = [
                  "delete from comments where recipe_id = ?",
                  "delete from rel_recipe_cat where recipe_id = ?",
                  "delete from likes where recipe_id = ?",
                  "delete from favorites where recipe_id = ?",
                  "delete from recipe_images where recipe_id = ?",
                ];
                foreach ($queries as $sql){
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$recipeId]);
                }
                $conn->commit();

                //delete images
                $fileUpload = new FileUpload();
                $fileUpload->delete($toBeDeleted);

                return true;
            }catch (\Exception $e){
                echo 'deleteRecipe failed. ' . $e->getMessage();
                return false;
            } finally {
                $this->db->closeConnection();
            }
        }
        return false;
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
     * Add recipe to favorite list
     * @param string $username
     * @param int $recipeId
     * @return bool
     */
    public function addToFavorites(string $username, int $recipeId): bool
    {
        $userId = $this->userModel->getUserIdByUsername($username);
        if($userId){
            try{
                $conn = $this->db->getConnection();
                $sql = "insert into favorites(user_id, recipe_id) value (?, ?)";
                $stmt =  $conn->prepare($sql);
                $stmt->execute([$userId, $recipeId]);

                return true;
            }catch (\Exception $e){
                echo 'addToFavorites failed. ' . $e->getMessage();
                return false;
            } finally {
                $this->db->closeConnection();
            }
        }
        return false;
    }

    /**
     * Remove recipe from favorite list
     * @param int $favId
     * @return bool
     */
    public function removeFromFavorites(int $favId): bool
    {
        try{
            $conn = $this->db->getConnection();
            $sql = "delete from favorites where favorite_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$favId]);

            return true;
        }catch (\Exception $e){
            echo 'removeFromFavorites failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Add recipe to likes list
     * @param string $username
     * @param int $recipeId
     * @return bool
     */
    public function addToLikes(string $username, int $recipeId): bool
    {
        $userId = $this->userModel->getUserIdByUsername($username);
        if($userId){
            try{
                $conn = $this->db->getConnection();
                $sql = "insert into likes(user_id, recipe_id) value (?, ?)";
                $stmt =  $conn->prepare($sql);
                $stmt->execute([$userId, $recipeId]);

                return true;
            }catch (\Exception $e){
                echo 'addToLikes failed. ' . $e->getMessage();
                return false;
            } finally {
                $this->db->closeConnection();
            }
        }
        return false;
    }

    /**
     * Remove recipe from like list
     * @param int $likeId
     * @return bool
     */
    public function removeFromLikes(int $likeId): bool
    {
        try{
            $conn = $this->db->getConnection();
            $sql = "delete from likes where like_id = ?";
            $stmt =  $conn->prepare($sql);
            $stmt->execute([$likeId]);

            return true;
        }catch (\Exception $e){
            echo 'removeFromLikes failed. ' . $e->getMessage();
            return false;
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

    /**
     * Insert image_link with corresponding recipe_id
     * @param int $recipeId
     * @param array $images
     * @return bool
     */
    private function uploadImages(int $recipeId, array $images): bool
    {
        if(count($images) < 1){
            return false;
        }

        try{
            $conn = $this->db->getConnection();

            $sqlHead = /** @lang text */
                "insert into recipe_images(recipe_id, image_url) values ";
            $sqlData = array_fill(0, count($images), "($recipeId, ?)");

            $stmt =  $conn->prepare( $sqlHead . implode(', ', $sqlData));
            foreach ($images as $index => $value){
                $stmt->bindValue($index + 1, $value );
            }

            $stmt->execute();
            return true;
        }catch (\Exception $e){
            echo 'uploadImages failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Add categories of recipe to database
     * @param int $recipeId
     * @param array $cats
     * @return void
     */
    private function uploadCategories(int $recipeId, array $cats): void
    {
        try{
            $conn = $this->db->getConnection();

            $sqlHead = /** @lang text */
                "insert rel_recipe_cat(recipe_id, cat_id) values ";
            $sqlData = array_fill(0, count($cats), "($recipeId, ?)");

            $stmt =  $conn->prepare( $sqlHead . implode(', ', $sqlData));
            foreach ($cats as $index => $value){
                $stmt->bindValue($index + 1, $value );
            }
            $stmt->execute();
        }catch (\Exception $e){
            echo 'uploadCategories failed. ' . $e->getMessage();
        } finally {
            $this->db->closeConnection();
        }
    }

    /**
     * Check if user own the recipe or not
     * @param int $userId
     * @param int $recipeId
     * @return bool
     */
    private function existsByUserIdAndRecipeId(int $userId, int $recipeId): bool
    {
        try{
            $conn = $this->db->getConnection();

            $sql = "select count(*) as count from recipes where user_id = ? and recipe_id = ?";
            $stmt =  $conn->prepare( $sql);
            $stmt->execute([$userId, $recipeId]);

            $res = $stmt->fetch(2);

            return $res['count'] == 1;
        }catch (\Exception $e){
            echo 'existsByUserIdAndRecipeId failed. ' . $e->getMessage();
            return false;
        } finally {
            $this->db->closeConnection();
        }
    }


}