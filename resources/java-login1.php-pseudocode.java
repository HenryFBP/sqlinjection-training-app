String username = request.getParameter("username");
String pass = request.getParameter("pass");
String query = "SELECT * FROM users where username='" + username + "' AND password = '" + md5(pass) + "'";

