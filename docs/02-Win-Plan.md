# LUSONG Win Plan for PSC XI (2026)

## 0. Urgent: confirm the deadline today
- The DICT Region VII post for PSC XI listed applications from **Aug 27 to Sept 20, 2026**. Other regions run their own forms and timelines. Email **psc_inquiries@dict.gov.ph** and message your DICT Regional Office today to confirm your region's deadline, the exact concept note file name (the template shows `TeamName_Concept Note_PS...`; ask if "PS" refers to a problem statement number), and whether a problem-statement list exists. If it does, map LUSONG to the health or disaster statement.
- Confirmed PSC XI eligibility: senior high, college, and tech-voc students; ICT-enabled solutions (AI, IoT, Web3, blockchain named as examples).

## 1. Why LUSONG wins on the criteria
- **Viability 25%**: the problem is measured weekly by DOH; the solution uses existing public data and a known drug; the MVP needs no hardware.
- **Relevance 25%**: AI plus public data, BIMS and eGovPH integration, UHC Act 2028 interoperability. Judges from DICT will see a product that extends government platforms instead of duplicating them.
- **Impact 20%**: nearly 12,000 cases in 2026, deaths preventable with a PHP 10 antibiotic given on time. Quantify per pilot barangay.
- **Creativity 20%**: nobody in PSC history has pitched post-flood disease logistics. The insight is the 72-hour prophylaxis window.
- **Presentation 10%**: a live demo where a judge's phone receives the Tagalog SMS.
- Pattern from PSC 8, 9, and X: the last three national champions (Clarity, Kara, DiaSight) were all AI-first pure software serving an underserved Filipino user; DiaSight (health) also won Best Business Concept. LUSONG fits that pattern exactly while avoiding every overused PSC genre (sign language, farm monitoring, marketplaces, waste scanners, jeepney navigation, generic chatbots).

## 2. Name, tagline, logo
- Name: **LUSONG** (to wade into floodwater). Tagline: "Lumusong ka man, may bantay."
- Logo options: (a) a wave line that becomes a heartbeat; (b) a pair of legs in water inside a shield; (c) a map pin shaped like a droplet with a check mark. Use a two-color palette (deep blue, warm orange) and a wordmark readable at phone size. Best Logo is judged on originality 30%, branding and recall 30%, design 20%.

## 3. Eight-week sprint
| Week | Deliverable | Owner |
|---|---|---|
| 1 | 15 interviews scheduled and 8 done; DOH weekly lepto tables, PAGASA rainfall, GeoRiskPH layers downloaded | CEO, CPO |
| 2 | Interviews done; problem statement and user journeys; data pipeline skeleton | CEO, CTO |
| 3 | Exposure engine v1 (rules); dashboard map; first backtest | CTO |
| 4 | Risk model v2 (gradient boosting); backtest report with recall numbers | CTO |
| 5 | Offline BHW PWA; symptom screen; SMS gateway working | CTO, CPO |
| 6 | Multilingual advisories reviewed by RHU; LOI signed with one LGU | CEO, CPO |
| 7 | Live pilot in 1 to 2 barangays; collect numbers | all |
| 8 | Deck, demo rehearsal x10, video, logo, concept note updated | all |
If behind by week 5: cut the ML model (keep rules), cut Messenger (keep SMS), keep the offline BHW list and the live SMS demo at all costs.

## 4. MVP scope
- Must have: barangay map with exposure scoring, household list from a BIMS-style CSV, SMS blast with reply capture, offline BHW checklist and symptom screen, stock-versus-need panel, exportable report.
- Nice to have: ML risk ranking, Messenger channel, rain gauge ingestion, dengue module.
- Architecture: Python (FastAPI, scikit-learn or XGBoost, GeoPandas), PostgreSQL + PostGIS, React dashboard, PWA with IndexedDB sync, SMS gateway (Semaphore or a telco API).
- Stage fallback: pre-recorded flood scenario loaded locally; SMS demo sent over mobile data from a phone hotspot; screenshots video as last resort.

## 5. Validation playbook
- Interview: BHWs (what happens after a flood, how they find who waded), RHU physician (prophylaxis protocol, doxycycline stock, case reporting), MDRRMO (data they get, what they would pay for), barangay captain (who decides, how LDRRMF is spent), two families of past patients (timeline from flood to hospital).
- Ten questions: When was the last flood? Who waded? How did you find out? Who got doxycycline and when? How many cases reached the RHU? What forms did you fill out? What is your BHS internet like? What would you do with a list of exposed households within 12 hours? Who approves a PHP 60,000 subscription? What would make you say no?
- Traction to show at the pitch: interview count, LOI or MOA, BHWs trained, households enrolled, backtest recall, SMS delivered in pilot, a quote from a municipal health officer.

## 6. Five-minute pitch (11 slides)
1. Hook: "After Typhoon Uwan, 3.6 million Filipinos waded through floodwater. Two weeks later, the hospitals filled." 2. Problem with numbers. 3. Why existing tools stop at the hazard. 4. LUSONG in one sentence. 5. Live demo (75 seconds): pick a barangay, simulate 180 mm rainfall, map turns red, 1,240 exposed households, doxycycline need vs stock, tap, judge's phone buzzes with the SMS, BHW app shows the house list offline, one symptom report escalates to the RHU. 6. How it works (data, model, offline). 7. SDG 3.3 and 3.d, 11.5, 13.1. 8. Business model and pricing. 9. Market and traction. 10. Team and mentor. 11. Ask: pilot LGUs, DICT grant, DOH data access.

## 7. Video pitch (2 to 3 minutes)
Scene 1: footage of people wading (b-roll, 10 s). Scene 2: a BHW with a paper list (interview clip). Scene 3: screen capture of the demo loop. Scene 4: team on camera, one line each. Scene 5: tagline and logo. Record voiceover with a lapel mic in a quiet room; export 1080p; subtitles in English. Judged on structure 50%, audio-visual 30%, creativity 10%, presentation 10%.

## 8. Judge Q&A prep (short answers)
- Where does household data come from? BIMS export or a barangay census CSV; we never need names for the risk model, only locations and household size.
- Is doxycycline prophylaxis DOH policy? Yes, for people who waded in floods, prescribed through the RHU; LUSONG routes people to the RHU, it does not dispense.
- Why would an LGU pay? Prevention is a legal use of the 70% LDRRMF allocation, and one avoided dialysis case costs more than a year of LUSONG.
- What if there is no signal? The BHW app is offline-first; SMS reaches 2G phones.
- Accuracy of exposure scoring? Backtested against DOH weekly data; we present recall and false-positive rate honestly and the BHW list is a prioritization, not a diagnosis.
- Why not just a DOH advisory? Advisories are not targeted, not timed, and not logged.
- Data privacy? DPA registration, LGU data processing agreement, consent in enrollment SMS.
- Competition? Hazard platforms are data partners; telemedicine is urban; no one owns the post-flood response layer.
- Expansion? Dengue, diarrhea, heat illness, and ASEAN neighbors with endemic leptospirosis.

## 9. Risks
- Data access delays: start with public layers and a synthetic barangay dataset; secure MOU in parallel.
- Model overclaims: present as decision support; keep rules baseline.
- LGU procurement cycles: pilot free in year one with grant support; sign LOIs now for 2027 budgets.
- Team bandwidth: strict weekly scope; mentor as tie-breaker.

## 10. Submission checklist
- Registration via your DICT Regional Office form; school clearance; startup logo (PNG and vector); video pitch; pitch deck PDF; concept note as `TeamName_Concept Note_PSC XI` (confirm suffix); team of 3 students + 1 mentor; at least one female member encouraged; confirm 2026 dates with psc_inquiries@dict.gov.ph.
