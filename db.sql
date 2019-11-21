// trần khôi 1660281

CREATE DATABASE id7264102_web17ck1 
CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci';
USE id7264102_web17ck1;
CREATE TABLE myuser(
	userID int AUTO_INCREMENT PRIMARY KEY,
    email varchar(255),
    password varchar(255),
    username varchar(255),
    fullname varchar(255),
    phonenumber varchar(255)
);