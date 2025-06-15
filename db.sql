CREATE DATABASE recipe_system;
USE recipe_system;

CREATE TABLE users (
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    User_Name VARCHAR(50) NOT NULL UNIQUE,
    E_Mail VARCHAR(100) NOT NULL UNIQUE,
    First_Name VARCHAR(50) NOT NULL,
    Last_Name VARCHAR(50) NOT NULL,
    GSM_No VARCHAR(20) NOT NULL,
    Birth_Date DATE NOT NULL,
    Avatar BLOB NOT NULL,
    Password VARCHAR(255) NOT NULL
);

CREATE TABLE recipes (
    Recipe_ID INT AUTO_INCREMENT PRIMARY KEY,
    Recipe_Name VARCHAR(100) NOT NULL,
    Ingredients TEXT NOT NULL,
    Preparation_Method TEXT NOT NULL,
    Portion_Count INT NOT NULL,
    User_ID INT,
    FOREIGN KEY (User_ID) REFERENCES users(User_ID)
);