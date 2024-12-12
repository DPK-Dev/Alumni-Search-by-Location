-- Database related queries
CREATE DATABASE alumni_search; 
USE alumni_search;

CREATE TABLE users(
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `location` POINT NOT NULL,
    PRIMARY KEY(`id`),
    UNIQUE(`email`),
    SPATIAL(`location`) -- Spatial index for efficient geospatial queries
);

 CREATE TABLE alumni_networks(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

 CREATE TABLE user_networks(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    network_id INT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(network_id) REFERENCES alumni_networks(id) ON DELETE CASCADE,
    INDEX(network_id, user_id) -- Index on network_id and user_id to optimize queries that filter by these columns
);