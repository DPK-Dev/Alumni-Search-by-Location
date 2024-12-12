# Alumni Search API

This API allows users to manage alumni networks and perform geospatial searches for nearby alumni.

## Endpoints Overview

1. [Update User](#1-update-user)
   - Updates a user's details, location, and associated alumni networks.
2. [Search Nearby Alumni](#2-search-nearby-alumni)
   - Searches for alumni within a specified radius of the user's location.

---

## 1. Update User

### Endpoint
PATCH http://localhost/alumni/api/v1/updateUser.php

### Description
This endpoint updates a user's personal details, geolocation (latitude and longitude), and the alumni networks they are associated with.

### Request Format
- **Method**: `PATCH`
- **Headers**: 
  - Content-Type: `application/json`
- **Body**: JSON
  ```json
  {
      "id": 4,
      "name": "deepak",
      "email": "ryan.sally@google.net",
      "latitude": 17.928543251492,
      "longitude": 79.102567725571,
      "network_ids": [1, 2]
  }
  ```

## 2. Search Nearby Alumni
  POST http://localhost/alumni/api/v1/search.php

### Description
This endpoint searches for alumni within a specified radius of the user's current location. The results include the user's details, distance from the current user, and the networks they belong to.

### Request Format
- **Method**: `POST`
- **Headers**: 
  - Content-Type: `application/json`
- **Body**: JSON
  ```json
  {
      "user_id": 4,
      "radius": 10
  }
  ```


  

