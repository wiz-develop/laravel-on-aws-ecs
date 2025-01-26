-- NOTE: データベースの初期化処理を記述

-- 全てのテーブルを削除
drop database if exists laravel-on-aws-ecs;

-- データベースを作成
create database laravel-on-aws-ecs;

-- デフォルトのデータベースを指定
use laravel-on-aws-ecs;
