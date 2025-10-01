1. Authentication & Rider Management
rider_signup.php:

-SELECT id FROM riders WHERE phone = ? - Checks if a rider with the same phone already exists

-INSERT INTO riders (phone, name, password) VALUES (?, ?, ?) - Creates a new rider account

-rider_login.php:

-SELECT * FROM riders WHERE phone = ? - Retrieves rider information for login verification

2. Driver Registration & Document Management
driver_signup.php:

-INSERT INTO drivers (phone, name, nrc, dob, address, vehicle_type, license_number, license_expiry, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending') - Creates a new driver record

-INSERT INTO vehicles (driver_id, registration_no, vehicle_model, vehicle_year, vehicle_color, engine_number) VALUES (?, ?, ?, ?, ?, ?) - Adds vehicle information for the driver

-INSERT INTO driver_documents (driver_id, document_type, file_path) VALUES (?, ?, ?) - Stores driver document information

-INSERT INTO driver_documents (driver_id, document_type, file_path) VALUES (?, 'vehicle_photo', ?) - Specifically stores vehicle photos

3. **Authentication & Admin Management**
`SELECT id FROM admins WHERE username = '$username' OR email = '$email' LIMIT 1`

`INSERT INTO admins (username, password, name, email) VALUES (...)`

Admin login and account creation.

---

4. **Dashboard & Statistics**
- `SELECT COUNT(*) as total FROM riders`

- `SELECT COUNT(*) as total FROM drivers`

- `SELECT COUNT(*) as total FROM drivers WHERE status = 'pending'`

- `SELECT COUNT(*) as total FROM rides`

- `SELECT COUNT(*) as total FROM rides WHERE status = 'completed'`

- `SELECT COUNT(*) as total FROM rides WHERE status = 'pending'`

- `SELECT COUNT(*) as total FROM rides WHERE status = 'cancelled'`

- `SELECT COUNT(*) AS total_drivers FROM drivers WHERE status='approved' AND is_available=1`

- `SELECT COUNT(*) AS total_riders FROM riders`

Used for displaying totals and metrics on admin/driver dashboards.

---

5. **Rider Management**
- `SELECT * FROM riders ORDER BY created_at DESC`

- `SELECT * FROM riders WHERE id = $rider_id`

- `UPDATE riders SET name = ? WHERE id = ?`

- `UPDATE riders SET profile_picture = ?, name = ? WHERE id = ?`

Rider profile management and retrieval.

---

6. **Driver Management**
- `SELECT d.*, v.registration_no, v.vehicle_model FROM drivers d LEFT JOIN vehicles v ON d.id = v.driver_id`

- `UPDATE drivers SET status = '$status' WHERE id = $driver_id`

- `SELECT * FROM drivers WHERE id = ?`

- `SELECT * FROM vehicles WHERE driver_id = ?`

- `SELECT id, name, phone, status FROM drivers WHERE phone = ? AND name = ?`

- `SELECT status, name FROM drivers WHERE id = ?`

Driver approval, profile, and vehicle info retrieval.

---

7. **Ride Management**
- `SELECT r.*, d.name as driver_name, d.phone as driver_phone, rider.name as rider_name, rider.phone as rider_phone FROM rides r LEFT JOIN drivers d ON r.driver_id = d.id LEFT JOIN riders rider ON r.rider_id = rider.id ORDER BY r.created_at DESC`

- `UPDATE rides SET driver_id = ?, status = 'assigned', updated_at = NOW() WHERE id = ?`

- `UPDATE rides SET status = 'cancelled' WHERE id = $ride_id`

- `UPDATE rides SET status = 'in_progress' WHERE id = ? AND driver_id = ?`

- `UPDATE rides SET status = 'completed', end_time = NOW() WHERE id = ? AND driver_id = ?`

- `INSERT INTO rides (...) VALUES (...)`

Ride assignment, status updates, and retrieval.

---

8. **Notifications**
- `SELECT * FROM notifications WHERE rider_id = ? AND is_read = 0 ORDER BY created_at DESC`

- `UPDATE notifications SET is_read = 1 WHERE id = ?`

- `INSERT INTO notifications (rider_id, message, type) VALUES (?, ?, 'ride_accepted')`

- `INSERT INTO notifications (rider_id, message, type) VALUES (?, ?, 'ride_rejected')`

Notifications for riders (e.g., ride accepted/rejected).

---

9. **Driver Availability & Ride Requests**
- `SELECT COUNT(*) as new_requests FROM rides WHERE driver_id = ? AND status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)`

- `UPDATE drivers SET is_available = 0 WHERE id = ?`

- `UPDATE drivers SET is_available = 1 WHERE id = ?`

Driver availability and new ride request checks.

---

10. **Ride History**
- `SELECT rh.* FROM rides_history rh WHERE rh.rider_id = $rider_id ORDER BY rh.created_at DESC`

Retrieves ride history for a specific rider.

---

11. **Available Drivers for Ride Matching**
- `SELECT d.*, v.registration_no, v.vehicle_model FROM drivers d JOIN vehicles v ON d.id = v.driver_id WHERE d.status = 'approved' AND d.is_available = 1 AND d.vehicle_type = ?`

- `SELECT d.*, v.registration_no, v.vehicle_model FROM drivers d JOIN vehicles v ON d.id = v.driver_id WHERE d.status = 'approved' AND d.is_available = 1`

Used to find available drivers for ride requests.

---

12. **Revenue & Analytics**
- `SELECT SUM(fare) as total_income FROM rides WHERE status = 'completed'`

- `SELECT DATE(ride_date) as date, SUM(fare) * 0.2 as profit FROM rides WHERE status = 'completed' AND ride_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(ride_date) ORDER BY date DESC`

- `SELECT YEAR(ride_date) as year, MONTH(ride_date) as month, SUM(fare) * 0.2 as profit FROM rides WHERE status = 'completed' AND ride_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY YEAR(ride_date), MONTH(ride_date) ORDER BY year DESC, month DESC`

- `SELECT YEAR(ride_date) as year, SUM(fare) * 0.2 as profit FROM rides WHERE status = 'completed' GROUP BY YEAR(ride_date) ORDER BY year DESC`

Financial reporting and profit calculations.

---

13. **Profile Pictures**
- `SELECT profile_picture FROM drivers WHERE id = ?`

- `UPDATE drivers SET profile_picture = ? WHERE id = ?`

Handling driver profile images.

---

14. **Recent Rides**
- `SELECT r.id, r.rider_id, r.rider_name, r.driver_id, r.pickup_location, r.dropoff_location, r.fare, r.distance, r.duration, r.vehicle_type, r.passengers, r.status, r.ride_date, d.name AS driver_name FROM rides r LEFT JOIN drivers d ON r.driver_id = d.id ORDER BY r.ride_date DESC LIMIT 3`

- `SELECT * FROM rides WHERE driver_id = ? ORDER BY ride_date DESC LIMIT 5`

Fetching recent rides for dashboards.

---
13. Database Stored Procedures
Driver Rejection Procedure:

-CREATE PROCEDURE reject_driver - Deletes a driver and all related records (documents, vehicles) in a transaction
DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `reject_driver` (IN `driver_id` INT) BEGIN
-- Start transaction
START TRANSACTION;

-- Delete related records from driver_documents table
DELETE FROM driver_documents WHERE driver_id = driver_id;

-- Delete related records from vehicles table
DELETE FROM vehicles WHERE driver_id = driver_id;

-- Delete the driver record
DELETE FROM drivers WHERE id = driver_id;

-- Commit transaction
COMMIT;
END$$

DELIMITER ;


14. Database Triggers
Rider Triggers:

-CREATE TRIGGER after_rider_insert - Automatically creates a ride history entry when a new rider account is created
DELIMITER $$
CREATE TRIGGER `after_rider_insert` AFTER INSERT ON `riders` FOR EACH ROW BEGIN
INTO rides_history (rider_id, action, details)
VALUES (NEW.id, 'account_created', CONCAT('Rider account created for ', NEW.name, ' (', NEW.phone, ')'));
END
$$
DELIMITER ;

Ride Triggers:

-CREATE TRIGGER after_ride_cancelled - Creates a ride history entry when a ride is cancelled
DELIMITER $$
CREATE TRIGGER `after_ride_cancelled` AFTER UPDATE ON `rides` FOR EACH ROW BEGIN
IF OLD.status != 'cancelled' AND NEW.status = 'cancelled' THEN
INSERT INTO rides_history (rider_id, action, details)
VALUES (NEW.rider_id, 'ride_cancelled', 'Ride was cancelled');
END IF;
END
$$
DELIMITER ;

-CREATE TRIGGER after_ride_completed - Creates a detailed ride history entry when a ride is completed
DELIMITER $$
CREATE TRIGGER `after_ride_completed` AFTER UPDATE ON `rides` FOR EACH ROW BEGIN
IF OLD.status != 'completed' AND NEW.status = 'completed' THEN
INSERT INTO rides_history (rider_id, action, details)
VALUES (NEW.rider_id, 'ride_completed',
CONCAT('Ride completed. Actual fare: ', NEW.fare, ' MMK. Distance: ',
NEW.distance, ' km. Duration: ', NEW.duration, ' minutes'));
END IF;
END
$$
DELIMITER ;

-CREATE TRIGGER after_ride_requested - Creates a ride history entry when a new ride is requested

DELIMITER $$
CREATE TRIGGER `after_ride_requested` AFTER INSERT ON `rides` FOR EACH ROW BEGIN
INSERT INTO rides_history (rider_id, action, details)
VALUES (NEW.rider_id, 'ride_requested',
CONCAT('Ride requested from ', NEW.pickup_location, ' to ', NEW.dropoff_location,
'. Estimated fare: ', NEW.fare, ' MMK'));
END
$$
DELIMITER ;