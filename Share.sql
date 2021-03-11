-- phpMyAdmin SQL Dump
-- version 4.9.7deb1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 08 mars 2021 à 20:30
-- Version du serveur :  8.0.23-0ubuntu0.20.10.1
-- Version de PHP : 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `Share`
--

-- --------------------------------------------------------

--
-- Structure de la table `File`
--

CREATE TABLE `File` (
  `id` int NOT NULL,
  `location` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `extension` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `creatorId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='		a';

--
-- Déchargement des données de la table `File`
--

INSERT INTO `File` (`id`, `location`, `name`, `extension`, `creatorId`) VALUES
(1, 2, 'pavé', 'txt', 1);

-- --------------------------------------------------------

--
-- Structure de la table `Folder`
--

CREATE TABLE `Folder` (
  `id` int NOT NULL,
  `folderName` varchar(200) NOT NULL,
  `parentFolderId` int DEFAULT NULL,
  `groupId` int NOT NULL,
  `rootFoldrer` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `Folder`
--

INSERT INTO `Folder` (`id`, `folderName`, `parentFolderId`, `groupId`, `rootFoldrer`) VALUES
(1, 'root', NULL, 2, b'1'),
(2, 'dossier', 1, 2, b'0');

-- --------------------------------------------------------

--
-- Structure de la table `Groupe`
--

CREATE TABLE `Groupe` (
  `id` int NOT NULL,
  `groupName` varchar(45) NOT NULL,
  `groupDescription` varchar(512) DEFAULT NULL,
  `idCreator` int NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `Groupe`
--

INSERT INTO `Groupe` (`id`, `groupName`, `groupDescription`, `idCreator`, `lastUpdate`) VALUES
(2, 'First', 'test', 1, '0000-00-00 00:00:00'),
(3, 'a', ' jhkltgbdfkjlgnbdjkcslghbnkjdlqsghbnqdjkfgbljkqdfgbjkdfsbgjkfdsbfg ndsf gdcsqgk dfsjhg hjisklqxf gdhkjsflq fhjiklfdg jhsdikf gs ghljk hjkgd hcjksdg hlsjkdghj ldf', 1, '2021-03-06 13:05:24'),
(4, 'fqdqfdssf', 'efdqsgfdgvfds,n:bg,ndhsbngjk dsfjkhgfhdsjhkghdsflijkgh dfqjklgh dsiukfjgh fdjsklghf djsklgh fdjksh gjkdfshg jkqdlsfhgjklqsdh gjksdhjgkldqs hjgklqds hgkjlqds hgkjqdhj ghkj qhjkqgh jk qkjh hjk hjksg jkhlsfd uhkjg shjkls kjlhs jklhsq hkjl', 2, '2021-03-06 13:06:20');

-- --------------------------------------------------------

--
-- Structure de la table `GroupeJoin`
--

CREATE TABLE `GroupeJoin` (
  `id` int NOT NULL,
  `groupId` int NOT NULL,
  `userId` int NOT NULL,
  `status` enum('pending','accepted','refused','excluded','left') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `GroupeJoin`
--

INSERT INTO `GroupeJoin` (`id`, `groupId`, `userId`, `status`) VALUES
(1, 2, 2, 'accepted');

-- --------------------------------------------------------

--
-- Structure de la table `User`
--

CREATE TABLE `User` (
  `id` int NOT NULL,
  `userName` varchar(45) NOT NULL,
  `passWord` varchar(256) NOT NULL,
  `email` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `User`
--

INSERT INTO `User` (`id`, `userName`, `passWord`, `email`) VALUES
(1, 'Dimitri', '70c341322683448d8a26fa29c4a4780601767ef854580949e801cceb6c469cac', 'marquis.dimitri.co@gmail.com'),
(2, 'random', 'test', 'pasinvité@gmail.seul');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `File`
--
ALTER TABLE `File`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_idx` (`creatorId`),
  ADD KEY `emplacement` (`location`);

--
-- Index pour la table `Folder`
--
ALTER TABLE `Folder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acces_idx` (`groupId`),
  ADD KEY `parent_idx` (`parentFolderId`);

--
-- Index pour la table `Groupe`
--
ALTER TABLE `Groupe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_idx` (`idCreator`);

--
-- Index pour la table `GroupeJoin`
--
ALTER TABLE `GroupeJoin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_idx` (`userId`),
  ADD KEY `group_idx` (`groupId`);

--
-- Index pour la table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userName_UNIQUE` (`userName`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `File`
--
ALTER TABLE `File`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Folder`
--
ALTER TABLE `Folder`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `Groupe`
--
ALTER TABLE `Groupe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `GroupeJoin`
--
ALTER TABLE `GroupeJoin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `User`
--
ALTER TABLE `User`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `File`
--
ALTER TABLE `File`
  ADD CONSTRAINT `creator` FOREIGN KEY (`creatorId`) REFERENCES `User` (`id`),
  ADD CONSTRAINT `emplacement` FOREIGN KEY (`location`) REFERENCES `Folder` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `Folder`
--
ALTER TABLE `Folder`
  ADD CONSTRAINT `acces` FOREIGN KEY (`groupId`) REFERENCES `Groupe` (`id`),
  ADD CONSTRAINT `parent` FOREIGN KEY (`parentFolderId`) REFERENCES `Folder` (`id`);

--
-- Contraintes pour la table `Groupe`
--
ALTER TABLE `Groupe`
  ADD CONSTRAINT `groupCreator` FOREIGN KEY (`idCreator`) REFERENCES `User` (`id`);

--
-- Contraintes pour la table `GroupeJoin`
--
ALTER TABLE `GroupeJoin`
  ADD CONSTRAINT `group` FOREIGN KEY (`groupId`) REFERENCES `Groupe` (`id`),
  ADD CONSTRAINT `user` FOREIGN KEY (`userId`) REFERENCES `User` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
