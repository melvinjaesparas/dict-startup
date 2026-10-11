# PSC XI Submission Package — LUSONG

## Form answers (Startup Information)
- **What technology does your startup primarily leverage?** → **Artificial Intelligence (AI)**. If the form allows more than one, also tick **Software as a Service (SaaS)**.
- **What sector does your startup primarily address?** → **Health**.

## Files in this folder (team name used: `TeamLusong`; rerun the build script with `--team` to change it)
| Requirement | File | Status |
|---|---|---|
| Startup Logo (PNG, max 10 MB) | `TeamLusong_StartupLogo_PSC11.png` (2048×2048, transparent) | Ready |
| Concept Note (PDF) | `TeamLusong_ConceptNote_PSC11.pdf` (+ editable `.docx`) | Ready; fill in team details |
| Presentation Deck (PDF, under 30 slides, no animations) | `TeamLusong_PresentationDeck_PSC11.pdf` (16 slides, + editable `.pptx`) | Ready; fill in team details |
| School IDs (PDF) | not generated (per team) | — |
| School Logo (PNG) | not generated (per team) | — |

Extra logo variants: `logo_icon.png` (square icon), `logo_horizontal.png` (icon + wordmark), `logo_icon.svg` (vector source).

## Regenerate with your real team details (one command)
```bash
python3 tools/build_submission.py \
  --team "TeamLusong" --school "PHINMA Araullo University, Cabanatuan City" --region "Region III (Central Luzon)" \
  --members "Justine Tabunan (CEO)" "John Alvin D. Bondoc (CTO)" "Aurchie Keem N. Rodriguez (CPO/CMO)" \
  --mentor "Gabriel Nikiloz G. Sioson (CIT Faculty)" --email "info@astin.ph"
```
Outputs land in `submission/` with the correct PSC11 file names. Requires python-docx, python-pptx, LibreOffice, Node + Playwright (all present in this environment).

## Before you upload
1. Confirm your region's deadline and exact file-name convention with your DICT Regional Office or psc_inquiries@dict.gov.ph.
2. Team details are filled in (PHINMA Araullo University assumed from the mentor's phinmaed.com address; change with `--school` if different).
3. Re-check the statistics marked "estimate" and the sources without URLs (DOH, EDCOM II, GAA) in the concept note.
4. Keep the deck under 30 slides and free of animations, video, or sound (it already is).
