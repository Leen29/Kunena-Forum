CREATE TABLE IF NOT EXISTS `#__kunena_aliases`
(
    `alias` varchar(255) NOT NULL,
    `type`  varchar(10)  NOT NULL,
    `item`  varchar(32)  NOT NULL,
    `state` tinyint(4)   NOT NULL default '0',
    UNIQUE KEY `alias` (alias),
    KEY `state` (state),
    KEY `item` (item),
    KEY `type` (type)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_announcement`
(
    `id`           int(3)     NOT NULL auto_increment,
    `title`        tinytext   NOT NULL,
    `created_by`   int(11)    NOT NULL default '0',
    `sdescription` text       NOT NULL,
    `description`  text       NOT NULL,
    `created`      datetime            DEFAULT NULL,
    `published`    tinyint(1) NOT NULL default '0',
    `publish_up`   datetime            DEFAULT NULL,
    `publish_down` datetime            DEFAULT NULL,
    `ordering`     tinyint(4) NOT NULL default '0',
    `showdate`     tinyint(1) NOT NULL default '1',
    PRIMARY KEY (id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_attachments`
(
    `id`            int(11)      NOT NULL auto_increment,
    `mesid`         int(11)      NOT NULL default '0',
    `userid`        int(11)      NOT NULL default '0',
    `protected`     tinyint(4)   NOT NULL default '0',
    `hash`          char(32)     NULL,
    `size`          int(11)      NULL,
    `folder`        varchar(255) NOT NULL,
    `filetype`      varchar(20)  NOT NULL,
    `filename`      varchar(190) NOT NULL,
    `filename_real` varchar(190) NOT NULL default ''
        COMMENT 'Filename for downloads',
    `caption`       varchar(255) NOT NULL default '',
    `inline`        tinyint(4)   NOT NULL default '0',
    PRIMARY KEY (id),
    KEY `mesid` (mesid),
    KEY `userid` (userid),
    KEY `hash` (hash),
    KEY `filename` (filename),
    KEY `filename_real` (filename_real)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_categories`
(
    `id`               int(11)      NOT NULL auto_increment,
    `parent_id`        int(11)      NULL     default '0',
    `name`             tinytext     NULL,
    `alias`            varchar(255) NOT NULL,
    `icon`             varchar(20)  NOT NULL,
    `icon_id`          tinyint(4)   NOT NULL default '0',
    `locked`           tinyint(4)   NOT NULL default '0',
    `accesstype`       varchar(20)  NOT NULL default 'joomla.level',
    `access`           int(11)      NOT NULL default '0',
    `pub_access`       int(11)      NOT NULL default '1',
    `pub_recurse`      tinyint(4)   NULL     default '1',
    `admin_access`     int(11)      NOT NULL default '0',
    `admin_recurse`    tinyint(4)   NULL     default '1',
    `ordering`         int(11)      NOT NULL default '0',
    `published`        tinyint(4)   NOT NULL default '0',
    `channels`         text         NULL,
    `checked_out`      int(11)      NOT NULL default '0',
    `checked_out_time` datetime     NULL     DEFAULT NULL,
    `review`           tinyint(4)   NOT NULL default '0',
    `allow_anonymous`  tinyint(4)   NOT NULL default '0',
    `post_anonymous`   tinyint(4)   NOT NULL default '0',
    `hits`             int(11)      NOT NULL default '0',
    `description`      text         NOT NULL,
    `headerdesc`       text         NOT NULL,
    `topictemplate`    text         NOT NULL,
    `class_sfx`        varchar(20)  NOT NULL,
    `allow_polls`      tinyint(4)   NOT NULL default '0',
    `topic_ordering`   varchar(16)  NOT NULL default 'lastpost',
    `iconset`          varchar(255) NULL     DEFAULT NULL,
    `numTopics`        mediumint(8) NOT NULL default '0',
    `numPosts`         mediumint(8) NOT NULL default '0',
    `last_topic_id`    int(11)      NOT NULL default '0',
    `last_post_id`     int(11)      NOT NULL default '0',
    `last_post_time`   int(11)      NOT NULL default '0',
    `params`           text         NOT NULL,
    `allow_ratings`    tinyint(4)   NOT NULL default '0',
    PRIMARY KEY (id),
    KEY `parent_id` (parent_id),
    KEY `category_access` (accesstype, access),
    KEY `published_pubaccess_id` (published, pub_access, id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_configuration`
(
    `id`     int(11) NOT NULL default '0',
    `params` text    NULL,
    PRIMARY KEY (id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_topics`
(
    `id`                    int(11)    NOT NULL auto_increment,
    `category_id`           int(11)    NOT NULL default '0',
    `subject`               tinytext   NULL,
    `icon_id`               int(11)    NOT NULL default '0',
    `label_id`              int(11)    NOT NULL default '0',
    `locked`                tinyint(4) NOT NULL default '0',
    `hold`                  tinyint(4) NOT NULL default '0',
    `ordering`              int(11)    NOT NULL default '0',
    `posts`                 int(11)    NOT NULL default '0',
    `hits`                  int(11)    NOT NULL default '0',
    `attachments`           int(11)    NOT NULL default '0',
    `poll_id`               int(11)    NOT NULL default '0',
    `moved_id`              int(11)    NOT NULL default '0',
    `first_post_id`         int(11)    NOT NULL default '0',
    `first_post_time`       int(11)    NOT NULL default '0',
    `first_post_userid`     int(11)    NOT NULL default '0',
    `first_post_message`    text       NULL,
    `first_post_guest_name` tinytext   NULL,
    `last_post_id`          int(11)    NOT NULL default '0',
    `last_post_time`        int(11)    NOT NULL default '0',
    `last_post_userid`      int(11)    NOT NULL default '0',
    `last_post_message`     text       NULL,
    `last_post_guest_name`  tinytext   NULL,
    `rating`                tinyint(6) NOT NULL default '0',
    `params`                text       NULL,
    PRIMARY KEY (id),
    KEY `category_id` (category_id),
    KEY `locked` (locked),
    KEY `hold` (hold),
    KEY `posts` (posts),
    KEY `hits` (hits),
    KEY `first_post_userid` (first_post_userid),
    KEY `last_post_userid` (last_post_userid),
    KEY `first_post_time` (first_post_time),
    KEY `last_post_time` (last_post_time),
    KEY `last_post_id` (last_post_id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_messages`
(
    `id`              int(11)      NOT NULL auto_increment,
    `parent`          int(11)      NULL     default '0',
    `thread`          int(11)      NULL     default '0',
    `catid`           int(11)      NOT NULL default '0',
    `name`            tinytext     NULL,
    `userid`          int(11)      NOT NULL default '0',
    `email`           tinytext     NULL,
    `subject`         tinytext     NULL,
    `time`            int(11)      NOT NULL default '0',
    `ip`              varchar(128) NULL,
    `topic_emoticon`  int(11)      NOT NULL default '0',
    `locked`          tinyint(4)   NOT NULL default '0',
    `hold`            tinyint(4)   NOT NULL default '0',
    `ordering`        int(11)      NULL     default '0',
    `hits`            int(11)      NULL     default '0',
    `moved`           tinyint(4)   NULL     default '0',
    `modified_by`     int(7)       NULL,
    `modified_time`   int(11)      NULL,
    `modified_reason` tinytext     NULL,
    PRIMARY KEY (id),
    KEY `thread` (thread),
    KEY `ip` (ip),
    KEY `userid` (userid),
    KEY `locked` (locked),
    KEY `parent_hits` (parent, hits),
    KEY `catid_parent` (catid, parent),
    KEY `time_hold` (time, hold),
    KEY `hold` (hold)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_messages_text`
(
    `mesid`   int(11)    NOT NULL default '0',
    `message` mediumtext NOT NULL,
    PRIMARY KEY (mesid)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_polls`
(
    `id`             int(11)      NOT NULL auto_increment,
    `title`          varchar(150) NOT NULL,
    `threadid`       int(11)      NOT NULL,
    `polltimetolive` datetime     NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY `threadid` (threadid)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_polls_options`
(
    `id`     int(11)      NOT NULL auto_increment,
    `pollid` int(11)      NULL,
    `text`   varchar(100) NULL,
    `votes`  int(11)      NULL,
    PRIMARY KEY (id),
    KEY `pollid` (pollid)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_polls_users`
(
    `pollid`   int(11)   NULL,
    `userid`   int(11)   NULL,
    `votes`    int(11)   NULL,
    `lasttime` timestamp NOT NULL default '0000-00-00 00:00:00',
    `lastvote` int(11)   NULL,
    UNIQUE KEY `pollid` (pollid, userid)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_ranks`
(
    `rank_id`      mediumint(8) unsigned NOT NULL auto_increment,
    `rank_title`   varchar(255)          NOT NULL default '',
    `rank_min`     mediumint(8) unsigned NOT NULL default '0',
    `rank_special` tinyint(1) unsigned   NOT NULL default '0',
    `rank_image`   varchar(255)          NOT NULL default '',
    PRIMARY KEY (rank_id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_rate`
(
    `id`       int(11)               NOT NULL auto_increment,
    `topic_id` int(11)               NULL,
    `userid`   int(11)               NULL,
    `rate`     mediumint(8) unsigned NOT NULL default '0',
    `time`     DATETIME              NULL     DEFAULT NULL,
    PRIMARY KEY (id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_sessions`
(
    `userid`     int(11) NOT NULL default '0',
    `allowed`    text    NULL,
    `lasttime`   int(11) NOT NULL default '0',
    `readtopics` text    NULL,
    `currvisit`  int(11) NOT NULL default '0',
    PRIMARY KEY (userid),
    KEY `currvisit` (currvisit)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_smileys`
(
    `id`           int(4)      NOT NULL auto_increment,
    `code`         varchar(12) NOT NULL default '',
    `location`     varchar(50) NOT NULL default '',
    `greylocation` varchar(60) NOT NULL default '',
    `emoticonbar`  tinyint(4)  NOT NULL default '0',
    PRIMARY KEY (id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_thankyou`
(
    `postid`       int(11)  NOT NULL,
    `userid`       int(11)  NOT NULL,
    `targetuserid` int(11)  NOT NULL,
    `time`         datetime NULL DEFAULT NULL,
    UNIQUE KEY `postid` (postid, userid),
    KEY `userid` (userid),
    KEY `targetuserid` (targetuserid)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_user_categories`
(
    `user_id`     int(11)    NOT NULL,
    `category_id` int(11)    NOT NULL,
    `role`        tinyint(4) NOT NULL default '0',
    `allreadtime` int(11)    NOT NULL default '0',
    `subscribed`  tinyint(4) NOT NULL default '0',
    `params`      text       NOT NULL,
    PRIMARY KEY (user_id, category_id),
    KEY `category_subscribed` (category_id, subscribed),
    KEY `role` (role)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_user_read`
(
    `user_id`     int(11) NOT NULL,
    `topic_id`    int(11) NOT NULL,
    `category_id` int(11) NOT NULL,
    `message_id`  int(11) NOT NULL,
    `time`        int(11) NOT NULL,
    UNIQUE KEY `user_topic_id` (user_id, topic_id),
    KEY `category_user_id` (category_id, user_id),
    KEY `time` (time)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_user_topics`
(
    `user_id`      int(11)      NOT NULL default '0',
    `topic_id`     int(11)      NOT NULL default '0',
    `category_id`  int(11)      NOT NULL,
    `posts`        mediumint(8) NOT NULL default '0',
    `last_post_id` int(11)      NOT NULL default '0',
    `owner`        tinyint(4)   NOT NULL default '0',
    `favorite`     tinyint(4)   NOT NULL default '0',
    `subscribed`   tinyint(4)   NOT NULL default '0',
    `params`       text         NULL,
    UNIQUE KEY `user_topic_id` (user_id, topic_id),
    KEY `topic_id` (topic_id),
    KEY `posts` (posts),
    KEY `owner` (owner),
    KEY `favorite` (favorite),
    KEY `subscribed` (subscribed)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_users`
(
    `userid`           int(11)      NOT NULL default '0',
    `status`           tinyint(1)   NOT NULL DEFAULT '0',
    `status_text`      varchar(255) NOT NULL DEFAULT '',
    `view`             varchar(8)   NOT NULL default '',
    `signature`        text         NULL,
    `moderator`        int(11)      NULL     default '0',
    `banned`           datetime     NULL     DEFAULT NULL,
    `ordering`         int(11)      NULL     default '0',
    `posts`            int(11)      NULL     default '0',
    `avatar`           varchar(255) NULL,
    `timestamp`        int(11)      NULL     default '0',
    `karma`            int(11)      NULL     default '0',
    `karma_time`       int(11)      NULL     default '0',
    `group_id`         int(4)       NULL     default '1',
    `uhits`            int(11)      NULL     default '0',
    `personalText`     tinytext     NULL,
    `gender`           tinyint(4)   NOT NULL default '0',
    `birthdate`        date         NULL     DEFAULT NULL,
    `location`         varchar(50)  NULL,
    `friendfeed`       varchar(75)  NULL,
    `icq`              varchar(75)  NULL,
    `bebo`             varchar(75)  NULL,
    `digg`             varchar(75)  NULL,
    `telegram`         varchar(75)  NULL,
    `vk`               varchar(75)  NULL,
    `microsoft`        varchar(75)  NULL,
    `skype`            varchar(75)  NULL,
    `twitter`          varchar(75)  NULL,
    `facebook`         varchar(75)  NULL,
    `google`           varchar(75)  NULL,
    `github`           varchar(75)  NULL,
    `myspace`          varchar(75)  NULL,
    `linkedin`         varchar(75)  NULL,
    `linkedin_company` varchar(75)  NULL,
    `delicious`        varchar(75)  NULL,
    `instagram`        varchar(75)  NULL,
    `qqsocial`         varchar(75)  NULL,
    `blogspot`         varchar(75)  NULL,
    `youtube`          varchar(75)  NULL,
    `reddit`           varchar(75)  NULL,
    `flickr`           varchar(75)  NULL,
    `apple`            varchar(75)  NULL,
    `qzone`            varchar(75)  NULL,
    `vimeo`            varchar(75)  NULL,
    `whatsapp`         varchar(25)  NULL,
    `weibo`            varchar(75)  NULL,
    `wechat`           varchar(75)  NULL,
    `yim`              varchar(75)  NULL,
    `websitename`      varchar(50)  NULL,
    `websiteurl`       varchar(50)  NULL,
    `rank`             tinyint(4)   NOT NULL default '0',
    `hideEmail`        tinyint(1)   NOT NULL default '1',
    `showOnline`       tinyint(1)   NOT NULL default '1',
    `canSubscribe`     tinyint(1)   NOT NULL default '-1',
    `userListtime`     int(11)      NULL     default '-2',
    `thankyou`         int(11)      NULL     default '0',
    `ip`               varchar(125) NULL,
    `socialshare`      tinyint(1)   NOT NULL default '1',
    PRIMARY KEY (userid),
    KEY `group_id` (group_id),
    KEY `posts` (posts),
    KEY `uhits` (uhits),
    KEY `banned` (banned),
    KEY `moderator` (moderator)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_users_banned`
(
    `id`             int(11)      NOT NULL auto_increment,
    `userid`         int(11)      NULL,
    `ip`             varchar(128) NULL,
    `blocked`        tinyint(4)   NOT NULL default '0',
    `expiration`     datetime     NULL     DEFAULT NULL,
    `created_by`     int(11)      NOT NULL,
    `created_time`   datetime     NULL     DEFAULT NULL,
    `reason_private` text         NULL,
    `reason_public`  text         NULL,
    `modified_by`    int(11)      NULL,
    `modified_time`  datetime     NULL     DEFAULT NULL,
    `comments`       text         NULL,
    `params`         text         NULL,
    PRIMARY KEY (id),
    KEY `userid` (userid),
    KEY `ip` (ip),
    KEY `expiration` (expiration),
    KEY `created_time` (created_time)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_logs`
(
    `id`          int(11)     NOT NULL auto_increment,
    `type`        tinyint(4)  NULL,
    `user_id`     int(11)     NULL,
    `category_id` int(11)     NULL,
    `topic_id`    int(11)     NULL,
    `target_user` int(11)     NULL,
    `ip`          varchar(40) NULL,
    `time`        int(11)     NULL,
    `operation`   varchar(40) NULL,
    `data`        mediumtext  NULL,
    PRIMARY KEY (id),
    KEY `type` (type),
    KEY `category_id` (category_id),
    KEY `topic_id` (topic_id),
    KEY `target_user` (target_user),
    KEY `user_id` (user_id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__kunena_version`
(
    `id`          int(11)     NOT NULL auto_increment,
    `version`     varchar(20) NOT NULL,
    `versiondate` date        NOT NULL,
    `installdate` date        NOT NULL,
    `build`       varchar(20) NOT NULL,
    `versionname` varchar(40) NULL,
    `sampledata`  tinyint(4)  NOT NULL default '0',
    `state`       text        NOT NULL,
    PRIMARY KEY (id)
)
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
