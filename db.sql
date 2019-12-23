-- trần khôi 1660281

CREATE DATABASE id7264102_web17ck1 
CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci';
USE id7264102_web17ck1;
CREATE TABLE myuser(
	userID int AUTO_INCREMENT PRIMARY KEY,
    status int null,
	email varchar(255),
    password varchar(255),
    username varchar(255),
    fullname varchar(255),
    phonenumber varchar(255),
	avatar varchar(255),
	code varchar(255)
);

CREATE TABLE mypost(
	postID int AUTO_INCREMENT PRIMARY KEY,
	userID int,
	content text,
	timecreate datetime
);
alter table mypost add foreign key(userID) references myuser(userID);

CREATE TABLE friends(
	userIDSend int not null,	-- id người gửi lời kết bạn
	userIDRecive int not null,	-- id người nhận lời mời
	timecreate datetime, -- ngày gửi lời mời,  nếu đã chấp nhận thì sẽ là ngày làm bạn
	status bit default 0,	-- 1(đã chấp nhận), 0: chưa chấp nhận
	PRIMARY KEY (userIDSend, userIDRecive)
);
alter table friends add foreign key(userIDSend) references myuser(userID);
alter table friends add foreign key(userIDRecive) references myuser(userID);


CREATE TABLE likes(
	postID int,  	-- post mà người dùng like
	userID int, 	-- người like post đó
	content text,
	timecreate datetime,
	PRIMARY key (postID, userID)
);
alter table likes add foreign key(userID) references myuser(userID);
alter table likes add foreign key(postID) references mypost(postID);

CREATE TABLE comments(
	commentID int AUTO_INCREMENT PRIMARY KEY,
	postID int,  -- post mà người dùng comment vào
	userID int, -- người comment
	content text,
	timecreate datetime
);
alter table comments add foreign key(userID) references myuser(userID);
alter table comments add foreign key(postID) references mypost(postID);
