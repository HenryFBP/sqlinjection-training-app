String uname = request.getParameter("username");
String pass = request.getParameter("pass");

uname = uname.replaceAll("[^a-z0-9]", ""); //remove all non-[a-z0-9] characters.

String query = "SELECT * FROM users where username='" + uname + "' AND password = '" + md5(pass) + "'";