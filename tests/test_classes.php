<?php
use ItvisionSy\SimpleORM\DataModel;

class Blog extends ItvisionSy\SimpleORM\DataModel {

    protected static $createdAtColumn = 'created_at';
    protected static $updatedAtColumn = 'updated_at';
    protected static $defaultValues = ['reads' => 0];
    protected static $tableName = 'blog';

    protected function filterOutReads(array $data) {
        $data['active'] = $data['reads'] > 0;
        return $data;
    }

    protected function filterOutBody(array $data) {
        $data['body'] = strip_tags($data['body']);
        return $data;
    }

    protected function filterOutDates(array $data) {
        $data['created_at'] = DateTime::createFromFormat('Y-m-d H:i:s', $data['created_at']);
        $data['updated_at'] = DateTime::createFromFormat('Y-m-d H:i:s', $data['updated_at']);
        return $data;
    }

    protected function filterInDates(array $data) {
        $data['created_at'] = $data['created_at'] instanceof DateTime ? $data['created_at']->format('Y-m-d H:i:s') : $data['created_at'];
        $data['updated_at'] = $data['updated_at'] instanceof DateTime ? $data['updated_at']->format('Y-m-d H:i:s') : $data['updated_at'];
        return $data;
    }

    public function increaseReads() {
        $this->reads++;
        $this->save();
    }

}

class FilteredBlog extends Blog {

    /**
     * 
     * @return DateTime
     */
    public function createdAt() {
        return $this->created_at;
    }

    public function preInsert(array &$data = array()) {
        $true = !empty($data['body']);
        return parent::preInsert($data) && $true;
    }

    public function preUpdate(array &$data = array()) {
        $true = !empty($data['body']);
        return parent::preUpdate($data) && $true;
    }

    public function preDelete() {
        $true = $this->createdAt()->getTimestamp() <= time() - 1 * 60 * 60;
        return parent::preDelete() && $true;
    }

}

class ReadBlog extends Blog {

    protected static $readOnly = true;

}

class UnexistedTable extends DataModel {
    
}
