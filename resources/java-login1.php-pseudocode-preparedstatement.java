String uname = request.getParameter("username");

PreparedStatement ps = conn.prepareStatement("SELECT * FROM users WHERE username = ?");

ps.setString(1, uname);

ResultSet rs = ps.executeQuery();