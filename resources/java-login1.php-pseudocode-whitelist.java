String username = request.getParameter("username");
String pass = request.getParameter("pass");

username = username.replaceAll("[^a-z0-9]", ""); //remove all non-[a-z0-9] characters.

String query = "SELECT * FROM users where username='" + username + "' AND password = '" + md5(pass) + "'";