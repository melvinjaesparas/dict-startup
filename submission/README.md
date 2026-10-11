# PSC XI Submission Package — LUSONG

## Form answers (Startup Information)
- **What technology does your startup primarily leverage?** → **Artificial Intelligence (AI)**. If the form allows more than one, also tick **Software as a Service (SaaS)**.
- **What sector does your startup primarily address?** → **Health**.

## Files in this folder (rename `TeamName` to your actual team name)
| Requirement | File | Status |
|---|---|---|
| Startup Logo (PNG, max 10 MB) | `TeamName_StartupLogo_PSC11.png` (2048×2048, transparent) | Ready |
| Concept Note (PDF) | `TeamName_ConceptNote_PSC11.pdf` (+ editable `.docx`) | Ready; fill in team details |
| Presentation Deck (PDF, under 30 slides, no animations) | `TeamName_PresentationDeck_PSC11.pdf` (16 slides, + editable `.pptx`) | Ready; fill in team details |
| School IDs (PDF) | not generated (per team) | — |
| School Logo (PNG) | not generated (per team) | — |

Extra logo variants: `logo_icon.png` (square icon), `logo_horizontal.png` (icon + wordmark), `logo_icon.svg` (vector source).

## Regenerate with your real team details (one command)
```bash
python3 tools/build_submission.py \
  --team "TeamName" --school "Your School" --region "Region IV-A" \
  --members "Juan Dela Cruz (CEO)" "Maria Santos (CTO)" "Jose Reyes (CPO/CMO)" \
  --mentor "Engr. Ana Lopez" --email "team@email.com"
```
Outputs land in `submission/` with the correct PSC11 file names. Requires python-docx, python-pptx, LibreOffice, Node + Playwright (all present in this environment).

## Before you upload
1. Confirm your region's deadline and exact file-name convention with your DICT Regional Office or psc_inquiries@dict.gov.ph.
2. Replace placeholder names in the concept note header and the Team slide.
3. Re-check the statistics marked "estimate" and the sources without URLs (DOH, EDCOM II, GAA) in the concept note.
4. Keep the deck under 30 slides and free of animations, video, or sound (it already is).
