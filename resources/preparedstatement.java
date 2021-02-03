String userdata = request.getParameter("username");

PreparedStatement ps = conn.prepareStatement("SELECT * FROM users WHERE username = ?");

ps.setString(1, userdata);

ResultSet rs = ps.executeQuery();

