<?php

namespace tests\unit;
class FileHelper
{
    public $assetsDir;
    public $arenaDir;
    public $archiveDir;
    
    public function __construct() 
    {
        $this->assetsDir = $GLOBALS['assets'];
        $this->arenaDir = $GLOBALS['files_arena'];
        $this->archiveDir = $GLOBALS['archives_arena'];
    }
    
    static public function create()
    {
        return new self();
    }
    
    static public function recursiveDelete($str) {
        if (is_file($str)) {
            return @unlink($str);
        }
        elseif (is_dir($str)) {
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path) {
                self::recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }       
    
    public function makeArenaFolder($folderName)
    {
        $pathname = $this->arenaDir.DIRECTORY_SEPARATOR.$folderName;
        mkdir($pathname);
        return $pathname;
    }
    
    public function clearFolder($path)
    {
        $scan = glob(rtrim($path,'/').'/*');
        foreach ($scan as $path){
            if (is_dir($path)){
                $this->recursiveDelete($path);
            } else {
                @unlink($path);
            }
        }
    }
    public function clearArena()
    {
        $this->clearFolder($this->arenaDir);
    }
    public function clearArchiveArena()
    {
        $this->clearFolder($this->archiveDir);
    }
    
    public function fillArena()
    {
        $arena = $this->arenaDir;
        $d = DIRECTORY_SEPARATOR;
        $this->copyTestFiles();
        
        $folder1 = $arena.$d.'folder1';
        @mkdir($folder1);
        $this->copyTestFiles($folder1);
        
        $folder1_1 = $folder1.$d.'folder1_1';
        @mkdir($folder1_1);
        $this->copyTestFiles($folder1_1);
        
        $folder1_2 = $folder1.$d.'folder1_2';
        @mkdir($folder1_2);
        $this->copyTestFiles($folder1_2);
        
        $folder2 = $arena.$d.'folder2';
        @mkdir($folder2);
        $this->copyTestFiles($folder2);
        
        $folder_empty = $arena.$d.'folder_empty';
        @mkdir($folder_empty);        
    }
    
    public function getLevelItems($level = 0, $directories = false, $files = true)
    {
        switch ($level) {
            case 0:
                $all = glob($this->arenaDir.'/*');

                break;
            case 1:
                $all1 = glob($this->arenaDir.'/folder1/*');
                $all2 = glob($this->arenaDir.'/folder2/*');
                
                $all = $all1 + $all2;
                break;
            case 2:
                $all1 = glob($this->arenaDir.'/folder1/folder1_1/*');
                $all2 = glob($this->arenaDir.'/folder1/folder1_2/*');
                $all = $all1 + $all2;
                break;
        }
        return $this->getItems($all, $directories, $files);
    }
    static public function getItems($all, $directories = false, $files = true)
    {
        
        if ($directories && $files) {
            return $all;
        }
        if ($directories){
            $items = array_filter($all, function($item){
                return is_dir($item);
            });
        } else {
            $items = array_filter($all, function($item){
                return !is_dir($item);
            });  
        }
        return $items;
    }
    
    
    protected function copyTestFiles($destDir = null)
    {
        if (!$destDir){
            $destDir = $this->arenaDir;
        }
        $entries = scandir($this->assetsDir);
        
        foreach ($entries as $entry){
            
            if ($entry == '.' || $entry == '..') continue;
            $source = $this->assetsDir.DIRECTORY_SEPARATOR.$entry;
            $dest = $destDir.DIRECTORY_SEPARATOR.$entry;
            if (!is_dir($source)){
                copy($source, $dest);
            }
        }        
    }
    
    public function getAddedFile()
    {
        return $this->assetsDir.'/add/added.txt';
    }
}