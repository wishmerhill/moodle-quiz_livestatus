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
  partial (~), wrong (✘) or not given yet (·). Questions are numbered as the
  student sees them. Only right or wrong is shown, never the answers
  themselves. This can be switched off in the settings.
- Page refreshes automatically every 10 seconds, plus a "Refresh now" button.
  Open rows and the scroll position are kept across refreshes.
- Respects separate groups.
- Languages: English, Italian.

## Question types

All the automatically graded question types of a standard Moodle 4.5 are
supported, and have been checked with correct, partially correct, wrong,
incomplete and missing answers:

true/false, multiple choice (one or more right answers), short answer,
numerical, calculated, simple calculated, calculated multichoice, matching,
random short-answer matching, select missing words, drag and drop into text,
drag and drop onto image, drag and drop markers, embedded answers (Cloze),
ordering.

An answer counts as *correct* when it gets full marks, *partial* when it gets
some marks, *wrong* when it gets none. Answers to multi-part questions (e.g.
matching, drag and drop, Cloze) with only some parts filled in count as partial.

Descriptions and manually graded questions (e.g. essays) are skipped.

Ordering questions: once the student moves on from the page, Moodle saves
the order shown as their answer even if they did not move anything, so from
then on the question counts as answered, as in Moodle's own grading.

## What the report can see

The report shows the answers the server has received: when the student changes
page, or through quiz autosave even without changing page
(`quiz | autosaveperiod` in the site administration). Lower the autosave period
to see answers sooner.

## Requirements

- Moodle 4.5 or later (tested on 4.5.15).

## Installation

### From the Moodle web interface (recommended)

1. Download `quiz_livestatus-X.Y.zip` from the
   [Releases](https://github.com/wishmerhill/moodle-quiz_livestatus/releases)
   page.
2. In Moodle, go to *Site administration > Plugins > Install plugins*, upload
   the ZIP and follow the steps.

Use the `quiz_livestatus-X.Y.zip` file, not the *Source code (zip)* archive
that GitHub adds to every release: Moodle needs the files inside a folder named
`livestatus`, and that archive uses a different name.

The same procedure updates an existing installation to a newer version.

### By hand

Copy the plugin files to `mod/quiz/report/livestatus` in your Moodle
installation, then visit *Site administration > Notifications* (or run
`php admin/cli/upgrade.php`).

The report then appears in the quiz *Results* menu as **Live status** for users
with the `mod/quiz:viewreports` capability.

## Settings

*Site administration > Plugins > Activity modules > Quiz > Live status*:

- **Progress bar colours**: segments (default) or single colour.
- **Show answers per question**: lets teachers expand a row (default: on).

## Changes

- **0.2** (beta): coloured progress bar, per-question details, site settings.
  Fix: an ordering question the student had not touched yet was counted as
  answered. First release with an installable ZIP.
- **0.1**: first version.

## Contributing

Contributions are welcome: bug reports, ideas, translations and pull requests,
especially from people who actually know Moodle's internals.

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
