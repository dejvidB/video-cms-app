# 🎥 Video CMS (Content Management System)

A simple, modular, and testable PHP application that demonstrates the architecture of a scalable video content management system with:

- ✅ RESTful API endpoints
- ✅ Redis-powered view tracking
- ✅ Elasticsearch-powered search
- ✅ RabbitMQ-powered async video processing
- ✅ Unit & integration test coverage using PHPUnit + Docker

---

## 🚀 Features

- 🧱 **Modular Architecture** – Clean separation of Controllers, Services, Repositories, Models
- 🐘 **Pure PHP** – No frameworks, just modern PHP 8.2+ (PSR-4, type safety, etc.)
- 📦 **Dockerized** – MySQL, Redis, RabbitMQ, and Elasticsearch containers
- 🔎 **Elasticsearch** – Search videos by title and URL
- 🔁 **RabbitMQ** – Simulated video processing queue (e.g., transcoding)
- 📈 **Redis** – Tracks views and trending videos
- 🧪 **Test Suite** – PHPUnit with full unit & integration tests

---

## ⚙️ Getting Started

### 1. Clone the project

```bash
git clone https://github.com/dejvidB/video-cms-app.git
cd video-cms-app
```

### 2. Run the application

```bash
docker compose up --build
```

The API will be available at:  
📍 `http://localhost:8080`

### 3. Seed some videos (optional)

Use `POST /videos` to add some videos, or write directly to MySQL.

---

## 🧪 Running Tests

### Unit tests (no containers needed)

```bash
composer tests:unit
```

### Full integration tests (Docker: MySQL + Redis + Elasticsearch)

```bash
composer tests
```

All tests run inside an **isolated test container** and clean up afterward.

---

## 📬 API Endpoints

| Method | Endpoint             | Description                     |
|--------|----------------------|---------------------------------|
| GET    | `/videos`            | List all videos                 |
| POST   | `/videos`            | Create a new video              |
| GET    | `/videos/{id}`       | Get single video + track view   |
| GET    | `/videos/search?q=`  | Search by title or URL          |
| GET    | `/videos/trending`   | List most viewed videos         |

All endpoints return JSON responses.

---

## 🧵 Running the Background Worker

To simulate video processing (e.g. encoding), the app uses a background job processor with RabbitMQ.

### 🔁 Start the worker:

```bash
php worker.php
```

The worker will:

- Listen for jobs on the `video_jobs` queue
- Simulate video processing with a short delay
- Update the video's status in the database to `"ready"`

Make sure your services are up with `docker compose up` **before running the worker**.

---

## 🛠️ Tech Stack

- **PHP 8.2+** – Type-safe
- **MySQL 8** – Video persistence
- **Redis** – View tracking, trending
- **Elasticsearch 8** – Fast search
- **RabbitMQ** – Background queue
- **Docker Compose** – Orchestration
- **PHPUnit** – Unit + integration tests
