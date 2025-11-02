# Xlack Technical Documentation

## Introduction

Xlack is a real-time messaging platform inspired by Slack, built with Laravel 11, Vue 3, and Inertia.js. It provides a robust, scalable, and feature-rich environment for team communication, supporting channels, direct messages, threads, user mentions, and more, all delivered in real-time via WebSockets.

## Key Objectives

- **Develop a modern, real-time messaging application:** Build a platform for seamless team communication with instant message delivery.
- **Support core collaboration features:** Implement essential functionalities like teams, channels, direct messages, threads, and user mentions.
- **Ensure a scalable and robust architecture:** Utilize Laravel's backend capabilities, including queues for background processing and events for broadcasting.
- **Deliver a fluid Single-Page Application (SPA) experience:** Use Vue.js and Inertia.js to create a responsive and interactive user interface without full page reloads.

## Use Cases

- **Team Collaboration:** Users can create or join teams to organize conversations and workflows.
- **Topic-Based Discussions:** Within a team, users can create public or private channels for specific topics.
- **Private Conversations:** Users can engage in one-on-one or small group conversations through Direct Messages (DMs).
- **Real-Time Messaging:** Messages are sent and received instantly without needing to refresh the page.
- **User Mentions:** Users can notify specific colleagues by mentioning them with `@username`, triggering a notification.
- **Threaded Replies:** Users can reply to specific messages, creating organized threads to keep conversations focused.
- **Simulated Video Calls:** Users can initiate a simulated video call from any chat window.

---

### High-Level Solution

Xlack is designed as a monolithic application with a Single-Page Application (SPA) frontend. Laravel serves as the backend API, while Inertia.js acts as a bridge to a Vue.js frontend, providing a seamless user experience.

- **Backend:** A standard Laravel application handles authentication, data persistence (MariaDB), and business logic.
- **Frontend:** Vue.js components render the UI, managed by Inertia.js, which receives data directly from Laravel controllers.
- **Real-Time Layer:** Laravel Echo, connected to a Soketi server, listens for broadcasted events (like new messages or notifications) and updates the UI in real-time.
- **Background Processing:** Heavy or non-blocking tasks, such as parsing user mentions in a new message, are offloaded to a Redis-backed queue.

### Flow Diagram: Message Sending

This diagram illustrates the flow of a message from submission to real-time delivery.

```mermaid
graph TD
  A["User submits message in UI"] --> B{"Controller"};
  B --> C["1. Save Message to DB"];
  B --> D["2. Dispatch ParseMentions Job"];
  C --> E{"Broadcast NewMessageSent Event"};
  D -- "Scans message & notifies user" --> F["3. Process Job in Background"];
  E -- "To all channel members" --> G["4. WebSocket Server (Soketi)"];
  G --> H["Frontend receives event via Echo"];
  H --> I["UI updates in real time"];
```

### Entity Diagram

```mermaid
erDiagram
    User {
        int id
        string name
        string email
    }
    Team {
        int id
        string name
    }
    Channel {
        int id
        string name
        int team_id
    }
    DmGroup {
        int id
        string name
    }
    Message {
        int id
        text content
        int user_id
        int parent_message_id FK
        string messageable_type
        int messageable_id
    }
    Notification {
        int id
        string type
        json data
        int user_id
    }

    User ||--|{ Team : "belongs to many"
    User ||--|{ Channel : "belongs to many"
    User ||--|{ DmGroup : "belongs to many"
    User ||--o{ Message : "sends"
    User ||--o{ Notification : "receives"
    Team ||--o{ Channel : "has many"
    
    Message }o--|| Channel : "can belong to"
    Message }o--|| DmGroup : "can belong to"
    Message }o--|| Message : "can be a reply to"
```

---

## Epics and Tasks

This is the high-level implementation plan used for Xlack, structured as Epics and Tasks:

- **Epic 1: Project Setup & Auth**
    - Task 1: Initialize Laravel, Sail, and Jetstream (Teams).
    - Task 2: Configure MariaDB, Redis, and Soketi.
    - Task 3: Set up Inertia.js, Vue 3, and TailwindCSS.
    - Task 4: Implement basic authentication and team switching.

- **Epic 2: Channels & DMs**
    - Task 1: Implement Channel CRUD (create, list, join).
    - Task 2: Implement Direct Messages (DMs) between users.
    - Task 3: Build the sidebar for channel and DM navigation.

- **Epic 3: Real-Time Messaging**
    - Task 1: Create Message model, controller, and database schema.
    - Task 2: Configure real-time broadcasting with Echo & Soketi.
    - Task 3: Develop the message input and display UI components.

- **Epic 4: Mentions & Notifications**
    - Task 1: Implement logic to parse `@mentions` in messages.
    - Task 2: Create a notification system (database and broadcast channels).
    - Task 3: Use a background job to handle mention parsing asynchronously.

- **Epic 5: Threads (Replies)**
    - Task 1: Adapt the `messages` table to support threaded replies (`parent_message_id`).
    - Task 2: Develop the thread modal and UI for viewing replies.
    - Task 3: Create API endpoints for fetching and creating thread replies.

- **Epic 6: Simulated Video Calls**
    - Task 1: Design and build the video call modal UI.
    - Task 2: Integrate the call initiation button into the chat window.
    - Task 3: Simulate call events in the UI (no real WebRTC integration).

- **Epic 7: Polish & Documentation**
    - Task 2: Implement scripts or commands for cleaning demo data.
    - Task 3: Write a comprehensive technical README.

---

## Technical Stack
- **Backend:** PHP 8.2+, Laravel 11, Laravel Sail (Docker), MariaDB, Redis
- **Frontend:** Vue 3, Inertia.js, TailwindCSS
- **Realtime:** Laravel Echo, Soketi/Pusher
- **Authentication:** Jetstream (Inertia, Teams)

## Installation and Setup

1. **Clone the repository:**
	```bash
	git clone https://github.com/alexisbanda/xlack.git
	cd xlack
	```
2. **Copy and edit the environment file:**
	```bash
	cp .env.example .env
	# Edit variables if needed (port, DB, etc)
	```
3. **Install dependencies and start services:**
	```bash
	./vendor/bin/sail up -d
	./vendor/bin/sail composer install
	./vendor/bin/sail npm install && ./vendor/bin/sail npm run build
	```
4. **Run database migrations:**
	```bash
	./vendor/bin/sail artisan migrate
	```
5. **(Optional) Seed example data:**
	```bash
	./vendor/bin/sail artisan db:seed
	```
6. **Start background workers:**
	- **Queue Worker:**
		```bash
		./vendor/bin/sail artisan queue:work --queue=default --tries=1
		```
	- **WebSocket Server:**
		```bash
		./vendor/bin/sail artisan reverb:start --host=0.0.0.0
		```
7. **Access the app:**
	- http://localhost

---

**Developed by Christian Banda.**
