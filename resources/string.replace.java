String userdata = request.getParameter("username");
userdata = userdata.replace("'", "\\'");
userdata = userdata.replace("(", "\\(");
userdata = userdata.replace(")", "\\)");
userdata = userdata.replace("\\", "\\\\");
// does this cover everything?

String query = "SELECT * FROM users WHERE username = '"+userdata+"'";

