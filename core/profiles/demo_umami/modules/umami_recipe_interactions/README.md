# Umami Recipe Interactions

This module provides simple recipe rating and favorites functionality for the
Umami demo profile. Authenticated users can rate each recipe from one to five
stars and mark recipes as favorites. Average ratings are stored and updated per
recipe while favorites are tracked per user.

The module exposes two JSON endpoints used by the accompanying JavaScript
library:

- `/recipe/{node}/rate` – Accepts `POST` with a `rating` parameter.
- `/recipe/{node}/favorite` – Toggles favorite status with a `POST` request.

The JavaScript behavior is bundled with the Umami theme and automatically
attached on recipe pages.
