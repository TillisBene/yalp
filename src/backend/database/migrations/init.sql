-- Create Database
    -- Gets created when the migrations are run

    -- Create Users Table
    CREATE TABLE users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        uuid CHAR(36) UNIQUE NOT NULL DEFAULT (UUID()),
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        login_code VARCHAR(255),
        first_login BOOLEAN DEFAULT TRUE,
        current_session VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        is_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        vector_coordinates JSON -- Store user vector data for algorithm
    );

    -- Create Devices Table
    CREATE TABLE devices (
        device_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        device_name VARCHAR(100),
        device_type VARCHAR(50),
        last_ip VARCHAR(45),
        last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        user_agent VARCHAR(255),
        refresh_token VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );

    -- Create Interests Table
    CREATE TABLE interests (
        interest_id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) UNIQUE NOT NULL,
        vector_coordinates JSON, -- Store interest vector data
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Create Posts Table
    CREATE TABLE posts (
        post_id INT PRIMARY KEY AUTO_INCREMENT,
        uuid CHAR(36) UNIQUE NOT NULL DEFAULT (UUID()),
        user_id INT,
        content TEXT,
        vector_coordinates JSON, -- Store post vector data
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );

    -- Create User Interests Table
    CREATE TABLE user_interests (
        user_id INT,
        interest_id INT,
        PRIMARY KEY (user_id, interest_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (interest_id) REFERENCES interests(interest_id)
    );

    -- Create Comments Table
    CREATE TABLE comments (
        comment_id INT PRIMARY KEY AUTO_INCREMENT,
        uuid CHAR(36) UNIQUE NOT NULL DEFAULT (UUID()),
        post_id INT,
        user_id INT,
        content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(post_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );

    -- Create Attachments Table
    CREATE TABLE attachments (
        attachment_id INT PRIMARY KEY AUTO_INCREMENT,
        uuid CHAR(36) UNIQUE NOT NULL DEFAULT (UUID()),
        post_id INT,
        file_url VARCHAR(255) NOT NULL,
        file_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(post_id)
    );

    -- Create Likes Table
    CREATE TABLE likes (
        user_id INT,
        post_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, post_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (post_id) REFERENCES posts(post_id)
    );

    -- Create Post Log Table
    CREATE TABLE post_logs (
        log_id INT PRIMARY KEY AUTO_INCREMENT,
        post_id INT,
        action VARCHAR(50),
        action_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(post_id)
    );

    -- Seen Posts Table
    CREATE TABLE seen_posts (
        user_id INT,
        post_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, post_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (post_id) REFERENCES posts(post_id)
    );

    -- Create Roles Table
    CREATE TABLE roles (
        role_id INT PRIMARY KEY AUTO_INCREMENT,
        role_name VARCHAR(50) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Create Permissions Table
    CREATE TABLE permissions (
        permission_id INT PRIMARY KEY AUTO_INCREMENT,
        role_id INT,
        permission_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(role_id)
    );

    -- Create User Roles Table
    CREATE TABLE user_roles (
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (role_id) REFERENCES roles(role_id)
    );

    -- Create User Permissions Table
    CREATE TABLE settings (
        setting_id INT PRIMARY KEY AUTO_INCREMENT,
        setting_name VARCHAR(255) NOT NULL,
        setting_desc TEXT,
        setting_default BOOLEAN DEFAULT FALSE
    );

    -- Create User Settings Table
    CREATE TABLE user_settings (
        user_id INT NOT NULL,
        setting_id INT NOT NULL,
        setting_set_to BOOLEAN NOT NULL,
        PRIMARY KEY (user_id, setting_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (setting_id) REFERENCES settings(setting_id)
    );

    -- Insert default roles
    INSERT INTO roles (role_name) VALUES 
    ('admin'),
    ('moderator'),
    ('super_moderator'),
    ('default');

    -- verify roles
    SELECT * FROM roles;

    INSERT INTO permissions (role_id, permission_name) VALUES
    ((SELECT role_id FROM roles WHERE role_name = 'admin'), 'can_delete_posts'),
    ((SELECT role_id FROM roles WHERE role_name = 'admin'), 'can_delete_users'),
    ((SELECT role_id FROM roles WHERE role_name = 'admin'), 'can_ban_users'),
    ((SELECT role_id FROM roles WHERE role_name = 'admin'), 'can_manage_roles'),
    ((SELECT role_id FROM roles WHERE role_name = 'moderator'), 'can_hide_posts'),
    ((SELECT role_id FROM roles WHERE role_name = 'moderator'), 'can_warn_users'),
    ((SELECT role_id FROM roles WHERE role_name = 'moderator'), 'can_delete_comments'),
    ((SELECT role_id FROM roles WHERE role_name = 'super_moderator'), 'can_delete_posts'),
    ((SELECT role_id FROM roles WHERE role_name = 'super_moderator'), 'can_hide_posts'),
    ((SELECT role_id FROM roles WHERE role_name = 'super_moderator'), 'can_warn_users'),
    ((SELECT role_id FROM roles WHERE role_name = 'super_moderator'), 'can_delete_comments'),
    ((SELECT role_id FROM roles WHERE role_name = 'default'), 'can_post'),
    ((SELECT role_id FROM roles WHERE role_name = 'default'), 'can_comment'),
    ((SELECT role_id FROM roles WHERE role_name = 'default'), 'can_like');

    -- verify permissions
    SELECT * FROM permissions;

    DELIMITER //
    CREATE TRIGGER after_user_insert 
    AFTER INSERT ON users
    FOR EACH ROW
    BEGIN
        INSERT INTO user_roles (user_id, role_id)
        SELECT NEW.user_id, role_id FROM roles WHERE role_name = 'default';
    END//
    DELIMITER ;
