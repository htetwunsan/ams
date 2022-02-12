<?php

namespace App\Migrations;

use App\Core\Database\Contracts\QueryBuilderContract;

class add_video_episode_count_to_episodes_table_0002
{
    public function __construct(
        protected QueryBuilderContract $qb
    ) {
    }

    public function up()
    {
        $this->qb->getPdo()->exec("CREATE TABLE tmp_episodes SELECT * FROM episodes");
        $this->qb->getPdo()->exec("ALTER TABLE episodes ADD COLUMN video_episode_count INT UNSIGNED NOT NULL AFTER video_description");
        $this->qb->getPdo()->exec("UPDATE episodes SET video_episode_count = (SELECT COUNT(id) FROM tmp_episodes as t WHERE t.video_cover = episodes.video_cover and t.tag = episodes.tag)");
        $this->qb->getPdo()->exec("DROP TABLE tmp_episodes");
    }

    public function down()
    {
        $this->qb->getPdo()->exec("ALTER TABLE episodes DROP COLUMN video_episode_count");
    }
}
