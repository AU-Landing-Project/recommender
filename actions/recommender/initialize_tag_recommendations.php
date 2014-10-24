<?php
/**
 * Cleanup and recalculate  Tag similarites / Tag recommendations
 */
cleanup_and_refresh_tags_similarities();
system_message(elgg_echo('recommender:message:configuration:tag_recommendations_initialized'));
forward("{$CONFIG->wwwroot}admin/plugin_settings/recommender");