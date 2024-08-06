-- CREATE TABLES

-- Tables
CREATE TABLE PostalCode(postal_code char(5) NOT NULL,
    city varchar(255) NOT NULL,
    state varchar(255) NOT NULL,
    latitude varchar(255) NOT NULL,
    longitude varchar(255) NOT NULL,
    PRIMARY KEY (postal_code));

CREATE TABLE `User` (email varchar(255) NOT NULL, 
    password varchar(255) NOT NULL, 
    first_name varchar(255) NOT NULL,
    last_name varchar (255) NOT NULL, 
    nickname varchar(255) NOT NULL,
    postal_code char(5) NOT NULL,
    PRIMARY KEY (email),
    KEY (postal_code));


CREATE TABLE PhoneNumber(email varchar(255) NULL,
    number varchar(255) NOT NULL,
    number_type varchar(255) NULL,
    share_phone_number BIT (1) NULL,
    PRIMARY KEY (number),
    KEY (email));


CREATE TABLE Platform(platform_name varchar(255) NOT NULL,
    PRIMARY KEY(platform_name)
    );

CREATE TABLE Item(
    email VARCHAR(255) NOT NULL,
    item_no INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
    TYPE VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    game_platform VARCHAR(255) NULL,
    media VARCHAR(255) NULL,
    computer_platform VARCHAR(255) NULL,
    piece INT NULL,
    `condition` VARCHAR(255) NOT NULL,
    description VARCHAR(255) NULL,
    PRIMARY KEY(item_no),
    KEY(email),
    KEY(game_platform));

CREATE TABLE Swap(counterparty_email varchar(255) NOT NULL,
    proposer_email varchar(255) NOT NULL,
    desired_item_id int(16) unsigned NOT NULL,
    proposer_item_id int(16) unsigned NOT NULL,
    accepted_rejected_date Date NULL,
    proposal_date Date NULL,
    swap_status varchar(255) NOT NULL,
    swap_proposer_rating INT UNSIGNED NULL,
    swap_counterparty_rating INT UNSIGNED NULL,
    PRIMARY KEY(counterparty_email, proposer_email, desired_item_id, proposer_item_id),
    KEY(counterparty_email),
    KEY(proposer_email),
    KEY(desired_item_id),
    KEY(proposer_item_id));

-- Constraints  

ALTER TABLE PhoneNumber
ADD CONSTRAINT fk_PhoneNumber_email_User_email FOREIGN KEY (email)  REFERENCES User (email);

ALTER TABLE Item
ADD CONSTRAINT fk_Item_email_User_email FOREIGN KEY (email) REFERENCES User (email);

ALTER TABLE User
ADD CONSTRAINT fk_User_postalcode_PostalCode_postalcode FOREIGN KEY (postal_code) REFERENCES PostalCode (postal_code);

ALTER TABLE Item
ADD CONSTRAINT fk_Item_gameplatform_Platform_Platformname FOREIGN KEY (game_platform) REFERENCES Platform (platform_name);

ALTER TABLE Swap
ADD CONSTRAINT fk_Swap_counterpartyemail_Item_email FOREIGN KEY (counterparty_email) REFERENCES Item (email);

ALTER TABLE Swap
ADD CONSTRAINT fk_Swap_proposeremail_Item_email FOREIGN KEY (proposer_email) REFERENCES Item (email);

ALTER TABLE Swap
ADD CONSTRAINT fk_Swap_desireditemid_Item_itemNo FOREIGN KEY (desired_item_id) REFERENCES Item (item_no);

ALTER TABLE Swap
ADD CONSTRAINT fk_Swap_proposeritemid_Item_itemNo FOREIGN KEY (proposer_item_id) REFERENCES Item (item_no);

ALTER TABLE Swap
ADD CONSTRAINT ck_sameItemID CHECK (desired_item_id <> proposer_item_id);
ALTER TABLE Swap
ADD CONSTRAINT ck_samePartyEmail CHECK (counterparty_email <> proposer_email);