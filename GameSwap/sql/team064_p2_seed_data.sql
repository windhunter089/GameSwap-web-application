USE cs6400_sp22_team064;

INSERT INTO `postalcode` (`postal_code`, `city`, `state`, `latitude`, `longitude`) VALUES
                                                                                       ('00623', 'Cabo Rojo', 'PR', '18.08643', '-67.15222'),
                                                                                       ('02472', 'Watertown', 'MA', '42.371296', '-71.18196'),
                                                                                       ('11111', 'NISTEST1', 'LA', '38.8976', '-77.0366'),
                                                                                       ('14043', 'Depew', 'NY', '42.904958', '-78.7006'),
                                                                                       ('20227', 'Washington', 'DC', '38.893311', '-77.014647'),
                                                                                       ('22222', 'NIS_TEST2', 'LA', '39.9496', '-75.1503'),
                                                                                       ('55302', 'Annandale', 'MN', '45.246631', '-94.11692'),
                                                                                       ('72714', 'Bella Vista', 'AR', '36.458041', '-94.23551');

INSERT INTO `user` (`email`, `PASSWORD`, `first_name`, `last_name`, `nickname`, `postal_code`) VALUES
                                                                                                   ('nbasnet7@gatech.edu', 'nischalPwd', 'Nischal', 'Basnet', 'Nischal744', '55302'),
                                                                                                   ('testuser1@yahoo.com', 'testPwd1', 'Test1', 'User1', 'TU1', '11111'),
                                                                                                   ('testuser2@yahoo.com', 'testPwd2', 'Test2', 'User2', 'TU2', '22222'),
                                                                                                   ('testuser44@gmail.com', 'veryDifficultPwd', 'Who', 'This', 'It\'s Me', '20227'),
                                                                                                   ('user2@gatech.edu', 'user2Pwd', 'John', 'Doe', 'JohnDoe123', '02472'),
                                                                                                   ('user3@gatech.edu', 'NewPwd', 'NewFName', 'NewLName', 'New Nick Name', '55302'),
                                                                                                   ('user4@gmail.com', 'User4Pwd', 'User4Fname', 'User4Lname', 'User4', '72714');

INSERT INTO `phonenumber` (`email`, `number`, `number_type`, `share_phone_number`) VALUES
                                                                                       ('user2@gatech.edu', '111-222-3333', 'mobile', b'0'),
                                                                                       ('nbasnet7@gatech.edu', '123-456-7890', 'mobile', b'0'),
                                                                                       ('testuser44@gmail.com', '555-444-3333', 'Work', b'1'),
                                                                                       ('user4@gmail.com', '777-777-7777', 'Home', b'1');

INSERT INTO `platform` (`platform_name`) VALUES
                                             ('Nintendo'),
                                             ('PlayStation'),
                                             ('Xbox');

INSERT INTO `item` (`email`, `item_no`, `TYPE`, `title`, `game_platform`, `media`, `computer_platform`, `piece`, `condition`, `description`) VALUES
                                                                                                                                                 ('nbasnet7@gatech.edu', 9, 'Video Game', 'Fifa 22', 'PlayStation', 'Optical Disc', NULL, NULL, 'Like New', 'FIFA 22 used only few times'),
                                                                                                                                                 ('nbasnet7@gatech.edu', 10, 'Board Game', 'Ticket to Ride', NULL, NULL, NULL, 100, 'Moderately Used', 'Ticket to Ride Board Game'),
                                                                                                                                                 ('nbasnet7@gatech.edu', 11, 'Computer Game', 'F1 2021', NULL, NULL, 'Windows', NULL, 'Heavily Used', NULL),
                                                                                                                                                 ('user2@gatech.edu', 12, 'Computer Game', 'Arma 3', NULL, NULL, 'Windows', NULL, 'Lightly Used', 'Arma 3 no DLCs included.'),
                                                                                                                                                 ('user2@gatech.edu', 13, 'Board game', 'Betrayal at the house on the hill', NULL, NULL, NULL, NULL, 'Moderately Used', NULL),
                                                                                                                                                 ('nbasnet7@gatech.edu', 14, 'Computer Game', 'Hearts of Iron IV', NULL, NULL, 'Windows', NULL, 'Heavily Used', 'Hearts of Iron IV all DLCs included.'),
                                                                                                                                                 ('user2@gatech.edu', 15, 'Board Game', 'Catan', NULL, NULL, NULL, 44, 'Like New', NULL),
                                                                                                                                                 ('user4@gmail.com', 16, 'Video Game', 'NHL 22', 'Xbox', 'Optical Disc', NULL, NULL, 'Moderately Used', 'NHLGame'),
                                                                                                                                                 ('user2@gatech.edu', 17, 'Computer Game', 'Civilization V', NULL, NULL, 'Linux', NULL, 'Mint', 'Brand new mint copy of Sid Meier''s Civ V.'),
                                                                                                                                                 ('nbasnet7@gatech.edu', 18, 'Video Game', 'Uncharted 4', 'PlayStation', 'game card', NULL, NULL, 'Moderately Used', 'Uncharted 4 Game'),
                                                                                                                                                 ('testuser1@yahoo.com', 19, 'Board Game', 'Monopoly', NULL, NULL, NULL, 250, 'New', 'Monopoly Board Game - Classic Favorite'),
                                                                                                                                                 ('nbasnet7@gatech.edu', 21, 'Board game', 'Forbidden Desert', NULL, '', '', 123, 'Mint', 'Not opened. Comes with original packaging.');

INSERT INTO `swap` (`counterparty_email`, `proposer_email`, `desired_item_id`, `proposer_item_id`, `accepted_rejected_date`, `proposal_date`, `swap_status`, `swap_proposer_rating`, `swap_counterparty_rating`) VALUES
                                                                                                                                                                                                                     ('nbasnet7@gatech.edu', 'user2@gatech.edu', 11, 17, '2022-03-18', '2022-02-17', 'Accepted', 2, 3),
                                                                                                                                                                                                                     ('testuser1@yahoo.com', 'nbasnet7@gatech.edu', 19, 18, '2022-03-18', '2022-03-09', 'Accepted', 5, 3),
                                                                                                                                                                                                                     ('user2@gatech.edu', 'nbasnet7@gatech.edu', 12, 9, '2022-03-17', '2022-03-01', 'Pending', 3, 0),
                                                                                                                                                                                                                     ('user2@gatech.edu', 'nbasnet7@gatech.edu', 15, 14, '2022-03-17', '2022-03-10', 'Pending', 1, 1),
                                                                                                                                                                                                                     ('user2@gatech.edu', 'user4@gmail.com', 13, 16, '2022-03-19', '2022-02-26', 'Accepted', NULL, NULL);








