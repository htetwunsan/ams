<?php

namespace App\Migrations;

use App\Core\Database\Contracts\QueryBuilderContract;

class create_episodes_table_0001
{
    public function __construct(
        protected QueryBuilderContract $queryBuilder
    ) {
    }

    public function up()
    {
        $this->queryBuilder->getPdo()->exec(
            "CREATE TABLE IF NOT EXISTS episodes(
                id INT UNSIGNED AUTO_INCREMENT,
                slug VARCHAR(512) NOT NULL,
                tag VARCHAR(255) NOT NULL,
                video_cover VARCHAR(255) NOT NULL,
                video_title VARCHAR(255),
                video_description TEXT,
                original_id VARCHAR(255) NOT NULL,
                embed VARCHAR(1024) NOT NULL,
                name VARCHAR(512) NOT NULL,
                number INT UNSIGNED NOT NULL,
                image_src VARCHAR(512) NOT NULL,
                image_alt VARCHAR(512) NOT NULL,
                sub BOOLEAN NOT NULL,
                original_date TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE (slug, tag),
                INDEX (original_date)
            )"
        );
    }

    public function down()
    {
        $this->queryBuilder->getPdo()->exec(
            "DROP TABLE episodes"
        );
    }
}
