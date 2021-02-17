String uname = request.getParameter("username");
uname = uname.replace("'", "");
uname = uname.replace("(", "");
uname = uname.replace(")", "");
// does this cover everything?
// O'Reilly -> OReilly

String query = "SELECT * FROM users WHERE username = '" + uname + "'";