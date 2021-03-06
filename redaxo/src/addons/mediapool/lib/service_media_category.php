<?php

/**
 * @package redaxo\mediapool
 */
class rex_media_category_service
{
    /**
     * @param string                  $name   The name of the new category
     * @param rex_media_category|null $parent The category in which the new category should be created, or null for a top/root level category
     *
     * @return string A success message
     */
    public static function addCategory($name, $parent)
    {
        $db = rex_sql::factory();

        // root category
        $parent_id = 0;
        $path = '|';
        if ($parent) {
            $parent_id = $parent->getId();
            $path = $parent->getPath() . $parent->getId().'|';
        }

        $db->setTable(rex::getTablePrefix() . 'media_category');
        $db->setValue('name', $name);
        $db->setValue('parent_id', $parent_id);
        $db->setValue('path', $path);
        $db->addGlobalCreateFields();
        $db->addGlobalUpdateFields();

        $db->insert();

        rex_media_cache::deleteCategoryList($parent_id);

        return rex_i18n::msg('pool_kat_saved', $name);
    }

    /**
     * @param int $categoryId
     *
     * @return string A success message
     *
     * @throws rex_functional_exception
     */
    public static function deleteCategory($categoryId)
    {
        $gf = rex_sql::factory();
        $gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE category_id=?', [$categoryId]);
        $gd = rex_sql::factory();
        $gd->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE parent_id=?', [$categoryId]);
        if ($gf->getRows() == 0 && $gd->getRows() == 0) {
            $gf->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'media_category WHERE id=?', [$categoryId]);
            rex_media_cache::deleteCategory($categoryId);
            rex_media_cache::deleteLists();
        } else {
            throw new rex_functional_exception(rex_i18n::msg('pool_kat_not_deleted'));
        }

        return rex_i18n::msg('pool_kat_deleted');
    }

    /**
     * @param int   $categoryId The id of the category to edit
     * @param array $data       The category data
     *
     * @return string A success message
     */
    public static function editCategory($categoryId, array $data)
    {
        $cat_name = $data['name'];

        $db = rex_sql::factory();
        $db->setTable(rex::getTablePrefix() . 'media_category');
        $db->setWhere(['id' => $categoryId]);
        $db->setValue('name', $cat_name);
        $db->addGlobalUpdateFields();

        $db->update();

        rex_media_cache::deleteCategory($categoryId);
        return rex_i18n::msg('pool_kat_updated', $cat_name);
    }
}
