# Quiz live status (`quiz_livestatus`)

A quiz report for Moodle that shows teachers, while a quiz is running, how
each student is doing: how many saved answers are right, partially right,
wrong or still missing.

Nothing is written to the attempts. The last saved response of each question
is re-graded in memory only, so the quiz behaviour is not affected: with
deferred feedback, for example, students still see nothing until they submit.

## Features

- One row per attempt in progress or finished (previews excluded), with
  columns: student, attempt, state, answered, correct, partial, wrong,
  not answered, percentage, last saved.
- Page refreshes automatically every 10 seconds, plus a "Refresh now" button.
- Respects separate groups.
- Skips descriptions and manually graded questions (e.g. essays).
- Languages: English, Italian.

## What the report can see

The report shows the answers the server has received: when the student changes
page, or through quiz autosave even without changing page
(`quiz | autosaveperiod` in the site administration). Lower the autosave period
to see answers sooner.

## Requirements

- Moodle 4.5 or later.

## Installation

Copy this folder to `mod/quiz/report/livestatus` in your Moodle installation,
then visit *Site administration > Notifications* (or run
`php admin/cli/upgrade.php`).

The report then appears in the quiz *Results* menu as **Live status** for users
with the `mod/quiz:viewreports` capability.

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
