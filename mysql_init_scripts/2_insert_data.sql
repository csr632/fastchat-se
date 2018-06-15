insert into fastchat_db.users (userName, password, email, nickname, gender)
values ('t1', '111111', '1@a.com', 'n1', 'female');
insert into fastchat_db.users (userName, password, email, nickname, gender)
values ('t2', '222222', '2@b.com', 'n2', 'male');
insert into fastchat_db.users (userName, password, email, nickname, gender)
values ('t3', '333333', '3@c.com', 'n3', 'female');

insert into fastchat_db.friendship (userName, friendName)
values ('t1', 't3');
insert into fastchat_db.friendship (userName, friendName)
values ('t3', 't1');
insert into fastchat_db.friendship (userName, friendName)
values ('t2', 't3');
insert into fastchat_db.friendship (userName, friendName)
values ('t3', 't2');
