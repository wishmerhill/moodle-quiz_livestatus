# Quiz live status (`quiz_livestatus`)

A quiz report for Moodle that shows teachers, while a quiz is running, how
each student is doing: how many saved answers are right, partially right,
wrong or still missing.

Nothing is written to the attempts. The last saved response of each question
is re-graded in memory only, so the quiz behaviour is not affected: with
deferred feedback, for example, students still see nothing until they submit.

> **Vibe coded.** This plugin was written by an AI assistant (Claude, by
> Anthropic) following my instructions and tests; I did not write the code
> myself. It works on my Moodle 4.5 site, but review it before using it in
> production.

## Features

- One row per attempt in progress or finished (previews excluded), with
  columns: student, attempt, state, answered, correct, partial, wrong,
  not answered, percentage, last saved.
- Coloured progress bar in the "Answered" column, in pastel colours. Two styles,
  chosen in the settings:
  - *segments*: green for correct, yellow for partial and red for wrong answers,
    grey for the questions not answered yet;
  - *single colour*: the answered part is green if more than half of the answers
    given so far are correct (a partial answer counts as half), red otherwise.
- Click a row to see, question by question, which answers are correct (✔),
  partial (~), wrong (✘) or not given yet (·). Only right or wrong is shown,
  never the answers themselves. This can be switched off in the settings.
- Page refreshes automatically every 10 seconds, plus a "Refresh now" button.
  Open rows and the scroll position are kept across refreshes.
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

## Contributing

Contributions are welcome: bug reports, ideas, translations and pull requests,
especially from people who actually know Moodle's internals.

## Settings

*Site administration > Plugins > Activity modules > Quiz > Live status*:

- **Progress bar colours**: segments (default) or single colour.
- **Show answers per question**: lets teachers expand a row (default: on).

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
