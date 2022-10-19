<?php

// Конфигурация
class Config
{
    const DB_SERVER = 'localhost';
    const DB_NAME = 'gallery'; 
    const DB_USER = 'root';
    const DB_PASS = '';
    const MAX_FILE_SIZE = 5000000;
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/tiff'];
    const UPLOAD_DIR = 'data/uploads';
    const PHOTO_DIR = 'data/photos';
    const MAX_COMMENT_SIZE = 200;
}
?>