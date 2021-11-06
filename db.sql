-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2021 at 03:16 AM
-- Server version: 10.4.20-MariaDB
-- PHP Version: 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `forum`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `name` varchar(99) NOT NULL,
  `value` varchar(999) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('fcolor', '231'),
('fname', 'Blog T');

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` varchar(10) NOT NULL,
  `title` varchar(40) NOT NULL,
  `value` varchar(40) NOT NULL,
  `icon` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`id`, `title`, `value`, `icon`) VALUES
('54878', 'Contact', 'http://127.0.0.1/forum/page/contact', 'account-box'),
('5787', 'About', 'http://127.0.0.1/forum/page/about', 'information'),
('7542', 'Discord', 'https://discord.gg', 'discord');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `user` varchar(99) NOT NULL,
  `message` varchar(400) NOT NULL,
  `date` varchar(60) NOT NULL,
  `seen` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` varchar(99) NOT NULL,
  `title` varchar(90) NOT NULL,
  `purl` varchar(100) NOT NULL,
  `content` varchar(10000) NOT NULL,
  `creator` varchar(99) NOT NULL,
  `tags` varchar(40) NOT NULL,
  `fimgurl` varchar(200) NOT NULL,
  `views` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `purl`, `content`, `creator`, `tags`, `fimgurl`, `views`) VALUES
('ei9Y8d509c28896865f8640f328f30f15721', 'dfgdfg', 'ei9Y', '\n    gfhgfh', '1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', 'gfhgfh', '', ''),
('JUhi0cc175b9c0f1b6a831c399e269772661', 'a', 'JUhi', 'a\n    ', '1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', 'a', '', ''),
('mt1I46472c1ae12ac41cb3f1db676c6f7926', 'yuiy', 'mt1I', 'fghfh', '1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', 'h', '', ''),
('qFWP1f6cc4d7fb3bc7128d853896db23161b', 'h1XSSh1', 'qFWP', '<h1>XSS</h1>\n<h1>XSS</h1>\n<h1>XSS</h1>\n    ', '1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', '<h1>XSS</h1>,<h1>XSS</h1>,<h1>XSS</h1>', '', ''),
('vfuxa57a7357346c18b7592ea354351c386c', 'Demo Post', 'vfux', '# demo\n', '1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', 'tag1,css,sf sdf sd,gh', '', ''),
('Vw3X560fa0fd98ea9866df50dfec4ce93919', 'Test Post', 'Vw3X', '\n    ---\n__Advertisement :)__\n\n- __[pica](https://nodeca.github.io/pica/demo/)__ - high quality and fast image\n  resize in browser.\n- __[babelfish](https://github.com/nodeca/babelfish/)__ - developer friendly\n  i18n with plurals support and easy syntax.\n\nYou will like those projects!\n\n---\n\n# h1 Heading 8-)\n## h2 Heading\n### h3 Heading\n#### h4 Heading\n##### h5 Heading\n###### h6 Heading\n\n\n## Horizontal Rules\n\n___\n\n---\n\n***\n\n\n## Typographic replacements\n\nEnable typographer option to see result.\n\n(c) (C) (r) (R) (tm) (TM) (p) (P) +-\n\ntest.. test... test..... test?..... test!....\n\n!!!!!! ???? ,,  -- ---\n\n\"Smartypants, double quotes\" and \'single quotes\'\n\n\n## Emphasis\n\n**This is bold text**\n\n__This is bold text__\n\n*This is italic text*\n\n_This is italic text_\n\n~~Strikethrough~~\n\n\n## Blockquotes\n\n\n> Blockquotes can also be nested...\n>> ...by using additional greater-than signs right next to each other...\n> > > ...or with spaces between arrows.\n\n\n## Lists\n\nUnordered\n\n+ Create a list by starting a line with `+`, `-`, or `*`\n+ Sub-lists are made by indenting 2 spaces:\n  - Marker character change forces new list start:\n    * Ac tristique libero volutpat at\n    + Facilisis in pretium nisl aliquet\n    - Nulla volutpat aliquam velit\n+ Very easy!\n\nOrdered\n\n1. Lorem ipsum dolor sit amet\n2. Consectetur adipiscing elit\n3. Integer molestie lorem at massa\n\n\n1. You can use sequential numbers...\n1. ...or keep all the numbers as `1.`\n\nStart numbering with offset:\n\n57. foo\n1. bar\n\n\n## Code\n\nInline `code`\n\nIndented code\n\n    // Some comments\n    line 1 of code\n    line 2 of code\n    line 3 of code\n\n\nBlock code \"fences\"\n\n```\nSample text here...\n```\n\nSyntax highlighting\n\n``` js\nvar foo = function (bar) {\n  return bar++;\n};\n\nconsole.log(foo(5));\n```\n\n## Tables\n\n| Option | Description |\n| ------ | ----------- |\n| data   | path to data files to supply the data that will be passed into templates. |\n| engine | engine to be used for processing templates. Handlebars is the default. |\n| ext    | extension to be used for dest files. |\n\nRight aligned columns\n\n| Option | Description |\n| ------:| -----------:|\n| data   | path to data files to supply the data that will be passed into templates. |\n| engine | engine to be used for processing templates. Handlebars is the default. |\n| ext    | extension to be used for dest files. |\n\n\n## Links\n\n[link text](http://dev.nodeca.com)\n\n[link with title](http://nodeca.github.io/pica/demo/ \"title text!\")\n\nAutoconverted link https://github.com/nodeca/pica (enable linkify to see)\n\n\n## Images\n\n![Minion](https://octodex.github.com/images/minion.png)\n![Stormtroopocat](https://octodex.github.com/images/stormtroopocat.jpg \"The Stormtroopocat\")\n\nLike links, Images also have a footnote style syntax\n\n![Alt text][id]\n\nWith a reference later in the document defining the URL location:\n\n[id]: https://octodex.github.com/images/dojocat.jpg  \"The Dojocat\"\n\n\n## Plugins\n\nThe killer feature of `markdown-it` is very effective support of\n[syntax plugins](https://www.npmjs.org/browse/keyword/markdown-it-plugin).\n\n\n### [Emojies](https://github.com/markdown-it/markdown-it-emoji)\n\n> Classic markup: :wink: :crush: :cry: :tear: :laughing: :yum:\n>\n> Shortcuts (emoticons): :-) :-( 8-) ;)\n\nsee [how to change output](https://github.com/markdown-it/markdown-it-emoji#change-output) with twemoji.\n\n\n### [Subscript](https://github.com/markdown-it/markdown-it-sub) / [Superscript](https://github.com/markdown-it/markdown-it-sup)\n\n- 19^th^\n- H~2~O\n\n\n### [\\<ins>](https://github.com/markdown-it/markdown-it-ins)\n\n++Inserted text++\n\n\n### [\\<mark>](https://github.com/markdown-it/markdown-it-mark)\n\n==Marked text==\n\n\n### [Footnotes](https://github.com/markdown-it/markdown-it-footnote)\n\nFootnote 1 link[^first].\n\nFootnote 2 link[^second].\n\nInline footnote^[Text of inline footnote] definition.\n\nDuplicated footnote reference[^second].\n\n[^first]: Footnote **can have markup**\n\n    and multiple paragraphs.\n\n[^second]: Footnote text.\n\n\n### [Definition lists](https://github.com/markdown-it/markdown-it-deflist)\n\nTerm 1\n\n:   Definition 1\nwith lazy continuation.\n\nTerm 2 with *inline markup*\n\n:   Definition 2\n\n        { some code, part of Definition 2 }\n\n    Third paragraph of definition 2.\n\n_Compact style:_\n\nTerm 1\n  ~ Definition 1\n\nTerm 2\n  ~ Definition 2a\n  ~ Definition 2b\n\n\n### [Abbreviations](https://github.com/markdown-it/markdown-it-abbr)\n\nThis is HTML abbreviation example.\n\nIt converts \"HTML\", but keep intact partial entries like \"xxxHTMLyyy\" and so on.\n\n*[HTML]: Hyper Text Markup Language\n\n### [Custom containers](https://github.com/markdown-it/markdown-it-container)\n\n::: warning\n*here be dragons*\n:::\n', '1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', 'tag1,tag2,tag3', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `forid` varchar(40) NOT NULL,
  `reacted` varchar(40) NOT NULL,
  `action` varchar(20) NOT NULL,
  `id` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sideboxes`
--

CREATE TABLE `sideboxes` (
  `id` varchar(10) NOT NULL,
  `content` varchar(1000) NOT NULL,
  `location` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sideboxes`
--

INSERT INTO `sideboxes` (`id`, `content`, `location`) VALUES
('5845', '<h2>This is a side box (Homepage left)</h2>\r\n<p>HTML is also allowed</p>', 'homepage_left'),
('877875', '<h2>This is a side box</h2>\r\n<p>HTML is also allowed</p>', 'homepage_right'),
('878787', '<h2>This is a side box (Homepage right)</h2>\r\n<p>HTML is also allowed</p>', 'homepage_right');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(400) NOT NULL,
  `admin` varchar(9) NOT NULL DEFAULT 'false',
  `username` varchar(99) NOT NULL,
  `email` varchar(60) NOT NULL,
  `verified` varchar(9) NOT NULL DEFAULT 'false',
  `loginhash` varchar(400) NOT NULL,
  `meta` varchar(99) NOT NULL,
  `firstrun` varchar(99) NOT NULL DEFAULT 'true',
  `color` varchar(10) NOT NULL,
  `job` varchar(40) NOT NULL,
  `company` varchar(60) NOT NULL,
  `uname` varchar(20) NOT NULL,
  `theme` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `admin`, `username`, `email`, `verified`, `loginhash`, `meta`, `firstrun`, `color`, `job`, `company`, `uname`, `theme`) VALUES
('1bdcc3ef46ae9dda04a902bcc9925ec83a3fdf4d', 'false', ' Posandu Mapa <b>XSS</b>', 'demouser@gmail.com', 'false', '', 'Creating this <h1>XSS</h1>', 'false', '#ea0606', 'Founder ', 'Tronic247', 'tronic247', 'dark'),
('1', 'true', 'admin', 'codeforum@gmail.com', 'true', '    ', '', 'true', '', '', '', 'posandu', 'dark');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`,`title`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `purl` (`purl`),
  ADD UNIQUE KEY `content` (`content`) USING HASH;

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `sideboxes`
--
ALTER TABLE `sideboxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `id` (`id`,`username`,`email`),
  ADD UNIQUE KEY `uname` (`uname`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
