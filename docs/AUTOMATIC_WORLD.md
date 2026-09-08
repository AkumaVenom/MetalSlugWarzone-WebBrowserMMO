# Automatic world server — v0.8.4.6

The Windows XAMPP installer configures automatic background execution through
local, CSRF-protected Update / Repair. It obtains XAMPP's active PHP INI and
`php-win.exe`, registers a task using absolute paths from the actual game folder,
then starts it. PHP documents that php-win provides the CLI behavior without a
console: [PHP command-line binaries](https://www.php.net/manual/en/features.commandline.php).

Task identity is a deterministic hash of the installation path and Apache's
Windows SID. The windowless `world_task.js` adapter calls the native Task Scheduler
API through `wscript.exe`. It stores no passwords, changes no security policy and
requests only the current account at LeastPrivilege. A same-account schtasks
adapter is available when Script Host cannot return a result.

Desktop XAMPP uses InteractiveToken, so it reuses the account already running
Apache and does not require a separate batch login. Keep that Windows account
signed in; all game players may be offline and the screen may be locked. Built-in
service identities use ServiceAccount, and custom Apache service identities use
S4U. This avoids claiming desktop logout support for an interactive task.
Microsoft documents current-account registration and task ownership in
[task security contexts](https://learn.microsoft.com/en-us/windows/win32/taskschd/security-contexts-for-running-tasks),
and the API argument contract in
[RegisterTask](https://learn.microsoft.com/en-us/windows/win32/taskschd/taskfolder-registertask).
The adapter selects the corresponding API logon flag without requesting elevation.

Database maintenance finishes and releases its world lock before startup is
attempted. Failure is captured in the separate Automatic World panel and guarded
`world_install.local.php`; it cannot turn completed Fresh Install/Repair into a
failed database operation. Enable / Retry uses local access and CSRF protection
and never connects to or changes the database. Installation confirmation reports
its database checks separately from automatic execution health.

The task repeats every minute without a duration/end date, uses IgnoreNew for
multiple instances and PT0S for the execution time limit. The engine is a
windowless PHP process with bounded work, not a held-open Apache request. It exits
when the configured Apache TCP port closes. The next scheduled launch resumes
it after Apache returns. Settings follow the
[Windows task schema](https://learn.microsoft.com/en-us/windows/win32/taskschd/task-scheduler-schema).
Interactive, S4U and built-in-service definitions were validated against that XSD.

`includes/world_service.php` refuses HTTP execution. It loads only its own
installation's `world_server.local.php`, checks the game root, copies the captured
host/port/database/user values into its process environment, and loads bootstrap
relative to its own path. It cannot accidentally look for `htdocs/includes`.
The task uses the active PHP INI explicitly, so mysqli/mbstring and XAMPP's runtime
settings are available without relying on PATH or a command window's directory.

The process owns a local non-blocking file lock. World pulses additionally own the
existing per-database advisory lock and durable throttle. A duplicate launch exits
without simulation. Browser requests and the automatic process cannot multiply
world throughput. The server refreshes its SQL timezone each pulse and retains
canonical transaction-safe arrivals and the existing AI budgets.

MySQL errors produce a retry heartbeat and bounded reconnect delays up to 30
seconds. A code change is detected within 15 seconds and retires the process
between completed batches; native scheduling restarts it within the next minute.
Configuration generations prevent an old process from continuing against new
installation settings. Identical repeat repair keeps the same generation.
Schema repair takes the same world lock before modifying tables.

The generated local settings and health files start with an immediate HTTP 404 /
exit guard. They contain no database password. The read-only, loopback-only setup
status endpoint reads the background process's health file; it does not pulse or
launch the world. Registration alone and a process waiting for its first completed
world update both remain Starting. Missing/stale heartbeats and registration
errors are reported explicitly. Only local setup invokes Windows registration;
authenticated public gameplay still only uses the existing protected pulse route.

The obsolete worldworker PHP/batch launchers are removed from the release.
Existing copies can be deleted after closing their window. The separate legacy
activity viewer is optional and has no role in offline progression.

## Acceptance on the actual Windows host

Native Task Scheduler was unavailable in the development environment. After
Update / Repair, verify **Automatic World → Running automatically**. Close every
browser, leave Apache/MySQL running for 10–15 minutes, and check persisted AI
progress and strike reports after returning. Restart Apache with every browser
closed and verify automatic restart within the next scheduled minute. This is
the remaining native Windows gate; XML validation and Linux process tests are
not represented as a completed Windows installation test.
