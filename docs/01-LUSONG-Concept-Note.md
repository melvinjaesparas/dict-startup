Team Name: ____ | School: ____ | Region: ____ | Members: ____ | Mentor: ____

# CONCEPT NOTE

**Startup: LUSONG — Post-Flood Leptospirosis Early Warning and Prophylaxis Dispatch for Barangays**
*Tagline: "Lumusong ka man, may bantay."*

## I. SUMMARY

Every rainy season, millions of Filipinos wade through floodwater and thousands contract leptospirosis, a bacterial infection that is cheap to prevent and deadly when caught late. The Philippines reported about 4,859 leptospirosis cases between June 8 and September 4, 2025 alone, and 2026 is running about 46% above 2025 at nearly 12,000 cases after monsoon flooding [5][6]. Prevention exists: doxycycline post-exposure prophylaxis, recommended by the Department of Health for people who waded in floods, works best when taken within 24 to 72 hours of exposure. The problem is that no one knows, in time, which households waded.

LUSONG is an AI-assisted early warning and response platform that turns a flood event into a barangay-level action list within hours. It fuses rainfall and flood forecasts, hazard maps, and barangay household data to estimate which households were exposed, triggers targeted SMS advisories in Filipino and regional languages, tells Barangay Health Workers (BHWs) exactly which homes to visit, allocates doxycycline stock to where it is needed, and screens symptoms so suspected cases reach the Rural Health Unit before kidney failure sets in. The BHW app works fully offline, because only 6% of Philippine health facilities had internet connectivity as of 2024 [12].

Key value proposition: LUSONG is the only system that closes the loop from flood to exposed household to the 72-hour prophylaxis window to case detection, for a municipality price lower than the cost of treating a handful of severe leptospirosis cases. Primary SDG priority: **SDG 3 (Good Health and Well-being), targets 3.3 and 3.d.**

## II. BACKGROUND OF THE PROBLEM

**Leptospirosis is a recurring, structural post-flood epidemic.** The Philippines recorded 3,037 leptospirosis cases from January to July 2025 and around 4,859 during the June 8 to September 4 rainy season [5]. By October 1, 2026 cases were near 12,000, 46% above the same period of 2025 [6]. A March 2026 paper in CDC Emerging Infectious Diseases, "Rethinking Leptospirosis Prevention, the Philippines," and a 2025 PMC review, "Leptospirosis in the Philippines: Confronting the Structural Roots of a Recurring Threat," both conclude that prevention fails for structural reasons: warnings are generic, prophylaxis arrives late, and surveillance only counts cases after they reach hospitals [7][8]. Severe cases require dialysis and intensive care, and deaths cluster among working-age men who waded to work, to clear drains, or to rescue neighbors.

**Flooding is getting bigger and more frequent.** Typhoon Uwan (November 2025) affected 3.6 million people in 9,643 barangays across 725 cities and municipalities and pre-emptively displaced almost 1.5 million people in 66 provinces [1][2]. Uwan and Tino together caused PHP 2.86 billion in agriculture and infrastructure damage [1]. Across 2025, cyclones and the southwest monsoon affected about 8.2 million people [3]. Every one of those events is followed, two to four weeks later, by a leptospirosis surge that the health system absorbs reactively.

**The frontline is thin and offline.** The country has 21.2 health workers per 10,000 people against the WHO benchmark of 44.5, with a projected shortage of about 290,000 [9][10]. Only 26,504 barangay health stations serve 42,026 barangays [11]. Only 6% of health facilities had internet connectivity as of 2024, and the Universal Health Care Act's target of interoperable electronic medical records is 2028 [12]. BHWs, the people who could deliver prophylaxis house to house, rely on paper lists and word of mouth.

**Existing tools stop at the hazard.** HazardHunterPH has 1.4 million users and GeoRiskPH offers excellent pre-event hazard maps [13]. PAGASA now integrates Google's AI flood and cyclone models into forecasting [14]. DOH issues leptospirosis advisories every rainy season. None of these converts a flood into a list of exposed households, a prophylaxis target, and a BHW route. The Department of the Interior and Local Government mandated the Barangay Information Management System (BIMS) for all barangays in December 2025, with more than 25,000 barangays oriented by mid-2026 [15]. For the first time, household-level barangay data exists digitally almost everywhere. Nobody has yet used it for post-flood health response.

**Relevance.** Leptospirosis sits at the intersection of the climate crisis (SDG 13), urban flooding (SDG 11), and primary health care (SDG 3). It is Filipino-specific in exposure pattern, language, and governance, which is why no global app covers it. It is also measurable within one rainy season, which makes it ideal for a startup that must prove impact fast.

## III. PROPOSED STARTUP SOLUTION

**What LUSONG is.** A software platform with three faces:

1. **LUSONG Command (LGU and RHU dashboard, web).** When PAGASA or the Municipal Disaster Risk Reduction and Management Office (MDRRMO) reports rainfall or flooding, LUSONG's exposure engine combines rainfall totals, flood hazard and depth maps (GeoRiskPH, NAMRIA, PAGASA, Google flood forecasts), elevation, drainage and known flood-prone sitios, and BIMS household locations to score every household's probability of floodwater exposure. The dashboard shows exposed households, people, and the doxycycline doses needed versus the stock at each barangay health station, and ranks barangays by expected cases using a model trained on past rainfall, flood, and DOH weekly case data.

2. **LUSONG Bantay (BHW mobile app, offline-first).** Each BHW receives a prioritized house-to-house list for their purok, a three-question Filipino symptom screen (fever, muscle pain especially the calves, red eyes or jaundice), a prophylaxis log with dosage guidance, and one-tap referral to the RHU. The app runs as a progressive web app on any Android phone, stores everything locally, and syncs when a signal appears.

3. **LUSONG Abiso (resident messaging).** Within hours of a flood, exposed households receive an SMS in their language: "LUSONG: Lumusong ka ba sa baha kahapon? Pumunta sa Barangay Health Station para sa libreng doxycycline ngayong araw. Kung may lagnat o sakit ng binti sa susunod na 2 linggo, magpakonsulta agad." Residents reply 1 or 2 to self-report wading and symptoms, feeding the BHW list. Channels: SMS first, then Messenger and the eGovPH super app.

**How it addresses the problem.** It converts hazard information into health action inside the clinical window, puts the decision in the hands of the BHW who is already trusted in the community, and gives the LGU a defensible, data-driven way to spend prevention funds. After the flood, it keeps counting symptoms for 30 days so clusters are found before they become deaths.

**Technology.** Python exposure and risk models (rule-based baseline plus gradient-boosted trees trained on historical rainfall, flood extent, and case data), PostGIS for spatial joins, a Node/React dashboard, a PWA with IndexedDB for offline BHW work, SMS via a local gateway, and a small language model for drafting advisories in Filipino, Cebuano, Hiligaynon, Bikol, and Waray with human sign-off. No hardware is required; the platform can later ingest LGU rain gauges or community sensors.

**SDG priority.** **Primary: SDG 3, target 3.3** (end the epidemics of water-borne and communicable diseases) **and target 3.d** (strengthen capacity for early warning, risk reduction, and management of health risks). Secondary: **SDG 11.5** (reduce deaths and people affected by disasters), **SDG 13.1** (strengthen resilience and adaptive capacity to climate-related hazards), and **SDG 6.2** (sanitation, through hotspot mapping of flood and waste exposure).

## IV. OBJECTIVES

1. **By the regional pitch (November 2026):** deliver a working MVP whose exposure engine is backtested on at least two 2024 to 2025 flood events, identifying at least 70% of barangays that subsequently reported leptospirosis clusters (team estimate; the model will be validated against DOH weekly surveillance data).
2. **By December 2026:** sign a Letter of Intent or Memorandum of Agreement with one flood-prone LGU, train at least 20 BHWs, and enroll at least 1,000 households in LUSONG Abiso.
3. **By June 2027:** run LUSONG live through one rainy-season flood in three LGUs, reaching at least 60% of estimated exposed households with an advisory within 24 hours and a prophylaxis offer within 72 hours.
4. **By December 2027:** reduce time from flood to first prophylaxis dose in pilot barangays from the current days-to-never baseline to under 48 hours, and record a measurable drop in severe (hospitalized) leptospirosis cases in pilot barangays versus matched control barangays.
5. **By December 2027:** reach 10 paying LGUs, PHP 600,000 in annual recurring revenue, and launch a second disease module (dengue or acute diarrhea) on the same platform.
6. **Throughout:** export every report in DOH Field Health Services Information System format and import from BIMS, so LGUs do not encode anything twice.

## V. TARGET MARKET / BENEFICIARIES

**Primary beneficiaries:** residents of flood-prone barangays, starting with the 9,643 barangays affected by Typhoon Uwan alone [2], and the working-age adults who wade most and die most. Nationally, flooding affected about 8.2 million people in 2025 [3].

**Primary users:** Barangay Health Workers and Rural Health Unit staff. There are 42,026 barangays and 26,504 barangay health stations [11]; each has at least one BHW. Secondary users: MDRRMO officers, municipal health officers, barangay captains.

**Paying customers:** city and municipal governments (about 1,640 LGUs nationally; team estimate from PSA counts), through the Local Disaster Risk Reduction and Management Fund, 70% of which is reserved for prevention, mitigation, and preparedness under RA 10121, and through the health budget. Later: provincial governments, DOH Centers for Health Development, water districts, and insurers.

**Early adopters:** LGUs with recurring flood and leptospirosis history and an active MDRRMO, for example Marikina, Malabon, Navotas, Calumpit, Hagonoy, Cabanatuan, Dagupan, Naga, Ormoc, Cotabato City, and Davao City riverside districts. These LGUs already have BIMS-oriented barangays and eLGU accounts (eLGU is live in 41 cities and 901 municipalities) [15].

## VI. VALUE PROPOSITION

| | HazardHunterPH / GeoRiskPH | PAGASA and Google flood forecasts | DOH seasonal advisories | BIMS / eLGU | Telemedicine apps (mWell, KonsultaMD) | **LUSONG** |
|---|---|---|---|---|---|---|
| Pre-event hazard maps | Yes | Yes | No | No | No | Uses them |
| Identifies exposed households after a flood | No | No | No | No | No | **Yes** |
| Targets prophylaxis within 72 hours | No | No | Generic text only | No | No | **Yes** |
| BHW house-to-house workflow, offline | No | No | No | Records only | No | **Yes** |
| Symptom screening and referral | No | No | No | No | Online, urban | **Yes, offline, rural** |
| Measures outcome per barangay | No | No | No | No | No | **Yes** |

Unique benefits: (1) the only tool built around the clinical prophylaxis window rather than the hazard; (2) offline-first for the 94% of facilities without connectivity; (3) uses data LGUs already have (BIMS) instead of asking them to encode; (4) one subscription covers warning, response, and reporting; (5) extensible to dengue, diarrhea, and heat illness, so an LGU buys a post-disaster health platform, not a single-disease app.

## VII. BUSINESS MODEL

**Value creation:** avoided hospitalizations, dialysis, and deaths; BHW hours saved; auditable prevention spending for LGUs.

**Revenue streams (prices in PHP, team estimates to be validated with LGU treasurers):**
- Municipal subscription: PHP 60,000 per year for up to 30 barangays, plus PHP 1,500 per additional barangay. Component cities: PHP 150,000 to 300,000 per year.
- SMS credits billed at cost plus 20%.
- Province-wide or DOH regional licenses at volume pricing.
- Implementation and training package: PHP 25,000 per LGU, one-time.
- Future: anonymized exposure and outbreak analytics for insurers and pharmaceutical distributors of doxycycline, under Data Privacy Act compliance.

**Unit economics sketch:** cloud and SMS cost per municipality around PHP 12,000 per year; onboarding effort about 3 person-days; gross margin above 70% after the first year. Ten LGUs cover the team's operating cost; fifty LGUs fund a full-time team of five.

**Sustainability beyond grants:** procurement through the LDRRMF prevention allocation is annual and renewable, and the product delivers a report the Commission on Audit and the Sangguniang Bayan can read. DICT Startup Grant Fund and DOST support would fund the first-year pilots so early LGUs pay a reduced rate and become reference customers.

## VIII. MARKET ANALYSIS

**Total addressable market (Philippines):** about 1,640 cities and municipalities at an average of PHP 80,000 per year equals **PHP 131 million per year** (estimate). Adding province and DOH regional licenses raises this to about PHP 180 million.

**Serviceable available market:** LGUs with recurring flood exposure, estimated at 600 municipalities and 60 cities (team estimate from NDRRMC-affected LGU counts; Uwan alone touched 725 cities and municipalities [2]): 600 × 60,000 + 60 × 200,000 = **PHP 48 million per year**.

**Serviceable obtainable market (3 years):** 100 LGUs = about **PHP 8 million per year**, plus SMS and training revenue.

**Demand signals:** leptospirosis cases rising 46% year on year [6]; PhilHealth's 2026 budget restoration (about PHP 129.8 billion) and the health sector budget of PHP 448 billion put money back into prevention [16]; DILG's BIMS mandate makes the data input available [15]; the UHC Act's 2028 interoperability deadline pushes LGUs to buy digital health tools [12]; the Philippines' 2026 ASEAN chairmanship under the theme "Navigating Our Future, Together" prioritizes digital public goods and climate resilience, and leptospirosis is endemic in Indonesia, Thailand, Malaysia, and Vietnam, making LUSONG exportable [17].

**Competition and positioning:** Government hazard platforms are complementary data sources, not competitors, and GeoRiskPH offers an API hub under MOU [13]. Telemedicine platforms are urban, online, and consumer-paid. Student and academic dengue tools focus on mosquito vectors. No Philippine startup or agency product found in our research targets leptospirosis exposure and prophylaxis logistics. LUSONG positions as "the post-flood health layer on top of the hazard layer."

## IX. OPERATIONS PLAN

**Team (3 students + mentor):**
- CEO: customer validation, LGU and RHU partnerships, pitch, finance.
- CTO: exposure and risk engine, backend, data pipelines, offline PWA.
- CPO/CMO: BHW user experience, multilingual advisory content, field training, logo, video, brand.
- Mentor (faculty, ideally with public health or GIS background): clinical validation, DOH and LGU introductions.
- Advisers to recruit: one municipal health officer, one MDRRMO officer, one BHW.

**8-week MVP build (to regional pitch):**
- Weeks 1 to 2: 15 interviews (5 BHWs, 3 RHU doctors or nurses, 3 MDRRMO officers, 2 barangay captains, 2 recent leptospirosis patients or families); collect DOH weekly surveillance tables, PAGASA rainfall, GeoRiskPH flood layers, sample BIMS export.
- Weeks 3 to 4: exposure engine and LGU dashboard; backtest on two 2024 to 2025 floods.
- Weeks 5 to 6: offline BHW app, SMS pipeline, multilingual advisories reviewed by the RHU.
- Week 7: two-week tabletop and live pilot in one or two barangays; collect usage and feedback.
- Week 8: polish, pitch deck, video pitch, logo, concept note update with pilot numbers.

**Validation and pilot:** secure a Letter of Intent from one LGU by week 6; enroll 20 BHWs and 1,000 households; measure advisory delivery time, BHW visits per day, prophylaxis doses logged, and symptom reports.

**Go-to-market:** DILG and DOH regional offices for endorsements; League of Municipalities and provincial health offices for bundled deals; DICT regional offices and the Startup Grant Fund for first-year subsidy; demonstrations timed before the next rainy season (May 2027).

**Partnerships:** GeoRiskPH (data MOU), PAGASA regional stations, DOH Epidemiology Bureau (surveillance data), local SMS gateway provider, Philippine Pharmacists Association or LGU pharmacies for doxycycline stock data, a university public health department for evaluation.

**Compliance:** Data Privacy Act registration, consent language in SMS enrollment, data processing agreement with each LGU, clinical content signed off by the municipal health officer.

## X. FINANCIAL REQUIREMENT

Twelve-month budget for MVP completion and pilots in three LGUs (PHP):

| Item | Amount | Use |
|---|---|---|
| Cloud, maps, and model hosting | 60,000 | Servers, PostGIS, storage, map tiles |
| SMS and messaging credits | 90,000 | About 150,000 messages for three pilot LGUs |
| Field validation and pilot travel | 80,000 | Interviews, BHW training days, flood-season deployments |
| BHW training materials and devices | 60,000 | Printed guides, five shared Android devices for BHS |
| Multilingual content and clinical review | 40,000 | Translation, RHU physician review honoraria |
| Legal, data privacy, and registration | 30,000 | DPA compliance, DTI and BIR registration |
| Student team stipends | 80,000 | Three students, part-time, 12 months |
| Contingency | 40,000 | Device loss, extra SMS during a major flood |
| **Total** | **480,000** | |

Allocation: 44% product and infrastructure, 38% field validation and adoption, 18% compliance and contingency. The DICT Startup Grant Fund (PHP 500,000 to 1 million) or PSC prize funding would cover this budget fully; DOST, CHED, or university incubator support would be used for year-two expansion to ten LGUs and the second disease module.

## Sources

[1] Manila Bulletin, Nov 14 2025, NDRRMC damage from Uwan and Tino: https://mb.com.ph/2025/11/14/agri-infra-damage-due-to-uwan-tino-hits-p28-billion-ndrrmc
[2] Rappler, Nov 12 2025, Uwan situational report: https://www.rappler.com/philippines/typhoon-uwan-situational-report-ndrrmc-november-12-2025/
[3] Context.news, flood control and the poor in the Philippines (2025 affected totals): https://www.context.news/climate-risks/how-flood-control-projects-fail-the-poor-in-the-philippines
[5] Outbreak News Today, nearly 4,900 leptospirosis cases this rainy season (2025): https://outbreaknewstoday.substack.com/p/philippines-reports-nearly-4900-leptospirosis
[6] Nature World News, Oct 1 2026, leptospirosis cases near 12,000, 46% above last year: https://www.natureworldnews.com/articles/73421/20261001/leptospirosis-cases-near-12000-philippines-running-46-above-last-year-after-monsoon-flooding.htm
[7] CDC Emerging Infectious Diseases, Mar 2026, Rethinking Leptospirosis Prevention, the Philippines: https://wwwnc.cdc.gov/eid/article/32/3/25-1250_article
[8] PMC, Leptospirosis in the Philippines: Confronting the Structural Roots of a Recurring Threat: https://pmc.ncbi.nlm.nih.gov/articles/PMC12567350/
[9] Health workforce density 21.2 per 10,000 vs WHO 44.5 (EDCOM II / DOH reporting, 2025)
[10] Projected health worker shortage about 290,000 (EDCOM II, 2025)
[11] 26,504 barangay health stations for 42,026 barangays (DOH, 2025)
[12] 6% of health facilities with internet connectivity (2024); UHC Act interoperable EMR target 2028
[13] PIA, Nov 2025, HazardHunterPH 1.4M users; GeoRiskPH API hub: https://pia.gov.ph/disaster-information-service/phivolcs-showcases-hazardhunterph-app-promotes-proactive-preparedness/
[14] Google AI flood and cyclone models integrated into PAGASA–Weathernews forecasting (late 2025)
[15] PIA, DILG nationwide LGUSS-BIMS adoption; eLGU coverage: https://pia.gov.ph/press-release/dilg-pushes-full-digital-shift-in-barangays-through-nationwide-lguss-bims-adoption/
[16] 2026 General Appropriations Act health allocations (PhilHealth about PHP 129.8B; health sector PHP 448.1B)
[17] BusinessWorld, Dec 29 2025, Philippine Startup Week 2026 aligned with ASEAN priorities: https://bworldonline.com/sparkup/2025/12/29/721115/philippine-startup-week-2026-to-align-with-asean-priorities-under-dict-led-isa-committee/

Items marked "team estimate" must be validated during weeks 1 to 2. Sources without URLs were taken from search summaries and should be re-verified against the original DOH, EDCOM II, and GAA documents before submission.
