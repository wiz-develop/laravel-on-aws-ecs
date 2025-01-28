# 🚀 Laravel on AWS ECS starter kit

## What's this ?
- Laravel + AWS ECS 構成でコンテナネイティブな Webアプリケーションを実装する際のスターターキットです。
- ローカル開発環境とAWS本番環境で全く同じDockerfileを使用するため、環境差による問題の発生を抑えることができます。
- Dev Container を採用しており、開発者毎の環境差も無くなります。
- AWS SQS 互換の ElasticMQ の Dockerimage を採用しており、ローカル環境でエミュレート可能です。

## Required Middleware
|       Middleware | Version           |
| ---------------: | :---------------- |
|         `Docker` | 27.1.x or higher  |
| `Docker Compose` | v2.29.x or higher |


## Summary
|   Service | Version |
| --------: | :------ |
|     `PHP` | 8.3.x   |
| `Laravel` | 11.x    |

## Directory Structure
|         Service | Role                            | Detail                                                                               |
| --------------: | :------------------------------ | :----------------------------------------------------------------------------------- |
| `.devcontainer` | Dev Container configurations    | 開発コンテナの設定ファイル/Dockerfileなど                                            |
|       `.vscode` | VSCode configurations           | VSCodeの設定ファイルなど                                                             |
|        `deploy` | Deployment for AWS Environment  | AWS運用環境に関するファイルなど<br> (e.x. CodePipeline, ECS Task definenation, etc.) |
|        `docker` | Docker files                    | ローカル環境・運用環境において共通のDockerファイル                                   |
|           `src` | Laravel Application Source Code | Laravel のソースコード                                                               |

## Docker Compose files

### [`compose.development.api.yaml`](./compose.development.api.yaml)
| Service | Role       | Browser               |
| ------: | :--------- | :-------------------- |
|   `web` | Web Server | http://localhost:8000 |
|   `app` | Web API    |                       |

### [`compose.development.batch.yaml`](./compose.development.batch.yaml)
|                Service | Role                     | Browser |
| ---------------------: | :----------------------- | :------ |
| `example-emails-batch` | CLI App for sending mail |         |

### [`compose.development.infra.yaml`](./compose.development.infra.yaml)
|     Service | Role                | Browser               |
| ----------: | :------------------ | :-------------------- |
|        `db` | Relational Database |                       |
|     `nosql` | NoSQL Database      |                       |
| `elasticmq` | Queuing             | http://localhost:9325 |
|      `mail` | Mail                | http://localhost:8025 |


### [`compose.development.worker.yaml`](./compose.development.worker.yaml)
|                 Service | Role                               | Browser                               |
| ----------------------: | :--------------------------------- | :------------------------------------ |
| `example-emails-worker` | Background Worker for sending mail | http://localhost:8000/api/send-emails |

### [`compose.production.yaml`](./compose.production.yaml)
|       Service | Role            | Browser               |
| ------------: | :-------------- | :-------------------- |
|         `web` | Web Server      | http://localhost:8000 |
|         `app` | Web API         |                       |
| `app-builder` | Web API Builder |                       |


