#!/usr/bin/env python3
"""Build the PSC XI submission package for LUSONG.

Usage (edit the defaults or pass flags):
  python3 tools/build_submission.py --team TeamName --school "School Name" --region "Region IV-A" \
      --members "Juan Dela Cruz (CEO)" "Maria Santos (CTO)" "Jose Reyes (CPO/CMO)" --mentor "Engr. Ana Lopez" \
      --email team@email.com
Outputs (in submission/):
  TeamName_StartupLogo_PSC11.png      startup logo (2048x2048, transparent)
  TeamName_ConceptNote_PSC11.docx/.pdf concept note following the official 10-section template
  TeamName_PresentationDeck_PSC11.pptx/.pdf initial presentation deck (16 slides)
"""
import argparse, os, re, shutil, subprocess, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
NAVY, ORANGE, LIGHT, INK, GREY = "0B3D91", "F26B21", "EAF2FF", "1A1A1A", "5B6B7F"

# ---------------------------------------------------------------- logo
LOGO_SVG = """<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1024 1024' width='{w}' height='{w}'>
<defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='#0E4DA4'/><stop offset='1' stop-color='#062B63'/></linearGradient>
<clipPath id='c'><rect x='112' y='112' width='800' height='800' rx='200'/></clipPath></defs>
<rect x='112' y='112' width='800' height='800' rx='200' fill='url(#g)'/>
<g clip-path='url(#c)'>
<path d='M60 660 C 180 570, 300 570, 420 660 S 660 750, 780 660 S 1000 570, 1060 660 L1060 1000 L60 1000 Z' fill='#1E6FD0' opacity='0.55'/>
<path d='M60 760 C 180 670, 300 670, 420 760 S 660 850, 780 760 S 1000 670, 1060 760 L1060 1000 L60 1000 Z' fill='#7FC4FF' opacity='0.75'/>
</g>
<polyline points='190,430 350,430 410,300 480,560 550,350 610,430 834,430' fill='none' stroke='#FF8A3D' stroke-width='46' stroke-linecap='round' stroke-linejoin='round'/>
<circle cx='834' cy='430' r='34' fill='#FFFFFF'/>
</svg>"""

LOGO_HTML = """<!doctype html><html><head><meta charset='utf-8'><style>
body{margin:0;background:transparent;font-family:'Inter','Inter Display',Arial,sans-serif}
#full{width:2048px;height:2048px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0}
#full .word{font-size:300px;font-weight:800;letter-spacing:14px;color:#0B3D91;line-height:1;margin-top:-40px}
#full .tag{font-size:72px;font-weight:500;color:#F26B21;margin-top:28px;letter-spacing:2px}
#icon{width:1024px;height:1024px}
#horizontal{width:2048px;height:640px;display:flex;align-items:center;gap:40px;padding-left:40px}
#horizontal .word{font-size:320px;font-weight:800;letter-spacing:12px;color:#0B3D91;line-height:1}
</style></head><body>
<div id='full'>{svg_big}<div class='word'>LUSONG</div><div class='tag'>Lumusong ka man, may bantay.</div></div>
<div id='icon'>{svg_icon}</div>
<div id='horizontal'>{svg_h}<div class='word'>LUSONG</div></div>
</body></html>"""

def build_logo(out_dir, team):
    html = LOGO_HTML.replace("{svg_big}", LOGO_SVG.format(w=1200)).replace("{svg_icon}", LOGO_SVG.format(w=1024)).replace("{svg_h}", LOGO_SVG.format(w=560))
    html_path = os.path.join(out_dir, "logo.html")
    with open(html_path, "w") as f: f.write(html)
    with open(os.path.join(out_dir, "logo_icon.svg"), "w") as f: f.write(LOGO_SVG.format(w=1024))
    subprocess.run(["node", os.path.join(ROOT, "tools", "render_logo.js"), html_path, out_dir], check=True)
    shutil.copy(os.path.join(out_dir, "logo_full.png"), os.path.join(out_dir, f"{team}_StartupLogo_PSC11.png"))
    os.remove(html_path)

# ---------------------------------------------------------------- concept note (DOCX from markdown)
from docx import Document
from docx.shared import Pt, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

INLINE = re.compile(r"(\*\*[^*]+\*\*|\*[^*]+\*)")

def add_inline(par, text, size=11, color=None):
    for part in INLINE.split(text):
        if not part: continue
        if part.startswith("**"):
            r = par.add_run(part[2:-2]); r.bold = True
        elif part.startswith("*"):
            r = par.add_run(part[1:-1]); r.italic = True
        else:
            r = par.add_run(part)
        r.font.size = Pt(size)
        if color: r.font.color.rgb = RGBColor.from_string(color)

def shade(cell, hex_color):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd"); shd.set(qn("w:val"), "clear"); shd.set(qn("w:color"), "auto"); shd.set(qn("w:fill"), hex_color)
    tcPr.append(shd)

def add_table(doc, rows):
    cols = len(rows[0])
    t = doc.add_table(rows=len(rows), cols=cols); t.style = "Table Grid"; t.alignment = WD_TABLE_ALIGNMENT.CENTER
    for i, row in enumerate(rows):
        for j, val in enumerate(row):
            cell = t.cell(i, j); cell.text = ""
            p = cell.paragraphs[0]; add_inline(p, val, size=9)
            if i == 0:
                shade(cell, NAVY)
                for r in p.runs: r.bold = True; r.font.color.rgb = RGBColor(255, 255, 255)
    doc.add_paragraph()

def build_concept_note(out_dir, team, school, region, members, mentor, email):
    md = open(os.path.join(ROOT, "docs", "01-LUSONG-Concept-Note.md")).read().splitlines()
    doc = Document()
    for s in doc.sections:
        s.left_margin = s.right_margin = Inches(1); s.top_margin = s.bottom_margin = Inches(1)
    st = doc.styles["Normal"]; st.font.name = "Arial"; st.font.size = Pt(11)
    st.element.rPr.rFonts.set(qn("w:eastAsia"), "Arial")
    # logo + title block
    logo = os.path.join(out_dir, "logo_horizontal.png")
    if os.path.exists(logo):
        p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER; p.add_run().add_picture(logo, width=Inches(2.6))
    p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("CONCEPT NOTE"); r.bold = True; r.font.size = Pt(14)
    p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("Philippine Startup Challenge XI (PSC XI)"); r.font.size = Pt(11); r.font.color.rgb = RGBColor.from_string(GREY)
    info = [("Team Name", team), ("Startup Name", "LUSONG"), ("School", school), ("Region", region),
            ("Student Members", "; ".join(members)), ("Mentor", mentor), ("Contact", email)]
    t = doc.add_table(rows=len(info), cols=2); t.style = "Table Grid"
    for i, (k, v) in enumerate(info):
        c0, c1 = t.cell(i, 0), t.cell(i, 1); c0.text = ""; c1.text = ""
        rr = c0.paragraphs[0].add_run(k); rr.bold = True; rr.font.size = Pt(10); shade(c0, LIGHT)
        add_inline(c1.paragraphs[0], v, size=10)
        c0.width = Inches(1.7); c1.width = Inches(4.8)
    doc.add_paragraph()
    table_buf, list_mode = [], None
    def flush_table():
        nonlocal table_buf
        if table_buf:
            rows = [[c.strip() for c in ln.strip().strip("|").split("|")] for ln in table_buf if not re.match(r"^\s*\|?\s*-{2,}", ln)]
            add_table(doc, rows); table_buf = []
    for ln in md[2:]:  # skip placeholder header and '# CONCEPT NOTE'
        if ln.startswith("|"):
            table_buf.append(ln); continue
        flush_table()
        if not ln.strip() or ln.startswith('# '):
            continue
        m = re.match(r"^## (?:([IVX]+)\. )?(.+)$", ln)
        if m:
            p = doc.add_paragraph(); p.paragraph_format.space_before = Pt(12); p.paragraph_format.space_after = Pt(4)
            if m.group(1):
                r = p.add_run(m.group(1) + ". "); r.font.size = Pt(11.5)
            r = p.add_run(m.group(2).upper()); r.bold = True; r.font.size = Pt(11.5); r.font.color.rgb = RGBColor.from_string(NAVY)
            continue
        if ln.startswith("**Startup:"):
            p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER; add_inline(p, ln, size=12); continue
        if ln.startswith("*Tagline"):
            p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER; add_inline(p, ln, size=10, color=ORANGE); continue
        if ln.startswith("- "):
            p = doc.add_paragraph(style="List Bullet"); add_inline(p, ln[2:]); continue
        mnum = re.match(r"^(\d+)\. (.+)$", ln)
        if mnum:
            p = doc.add_paragraph(); p.paragraph_format.left_indent = Inches(0.35); p.paragraph_format.first_line_indent = Inches(-0.3)
            p.paragraph_format.space_after = Pt(4); add_inline(p, mnum.group(1) + ".  " + mnum.group(2)); continue
        if re.match(r"^\[\d+\] ", ln):
            p = doc.add_paragraph(); p.paragraph_format.space_after = Pt(2); add_inline(p, ln, size=8.5, color=GREY); continue
        p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY; p.paragraph_format.space_after = Pt(6); add_inline(p, ln)
    flush_table()
    path = os.path.join(out_dir, f"{team}_ConceptNote_PSC11.docx"); doc.save(path); return path

# ---------------------------------------------------------------- deck (PPTX)
from pptx import Presentation
from pptx.util import Inches as PIn, Pt as PPt
from pptx.dml.color import RGBColor as PRGB
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def rgb(h): return PRGB.from_string(h)

class Deck:
    def __init__(self, team, school, members, mentor, email, logo_dir):
        self.prs = Presentation(); self.prs.slide_width = PIn(13.333); self.prs.slide_height = PIn(7.5)
        self.team, self.school, self.members, self.mentor, self.email = team, school, members, mentor, email
        self.icon = os.path.join(logo_dir, "logo_icon.png"); self.full = os.path.join(logo_dir, "logo_full.png"); self.n = 0
    def box(self, s, x, y, w, h, fill=None, line=None, shape=MSO_SHAPE.RECTANGLE):
        sh = s.shapes.add_shape(shape, PIn(x), PIn(y), PIn(w), PIn(h))
        if fill: sh.fill.solid(); sh.fill.fore_color.rgb = rgb(fill)
        else: sh.fill.background()
        if line: sh.line.color.rgb = rgb(line)
        else: sh.line.fill.background()
        sh.shadow.inherit = False; return sh
    def text(self, s, x, y, w, h, lines, size=18, color=INK, bold=False, align=PP_ALIGN.LEFT, anchor=MSO_ANCHOR.TOP, bullet=False, spacing=6):
        tb = s.shapes.add_textbox(PIn(x), PIn(y), PIn(w), PIn(h)); tf = tb.text_frame; tf.word_wrap = True; tf.vertical_anchor = anchor
        tf.margin_left = tf.margin_right = PIn(0.08); tf.margin_top = tf.margin_bottom = PIn(0.04)
        if isinstance(lines, str): lines = [lines]
        for i, ln in enumerate(lines):
            p = tf.paragraphs[0] if i == 0 else tf.add_paragraph(); p.alignment = align; p.space_after = PPt(spacing)
            b = bold; sz = size; col = color; txt = ln
            if isinstance(ln, tuple): txt, opts = ln; b = opts.get("bold", b); sz = opts.get("size", sz); col = opts.get("color", col)
            if bullet and not (isinstance(ln, tuple) and ln[1].get("nobullet")): txt = "•  " + txt
            r = p.add_run(); r.text = txt; r.font.size = PPt(sz); r.font.bold = b; r.font.color.rgb = rgb(col); r.font.name = "Arial"
        return tb
    def slide(self, title, subtitle=None):
        s = self.prs.slides.add_slide(self.prs.slide_layouts[6]); self.n += 1
        self.box(s, 0, 0, 13.333, 1.15, fill=NAVY)
        self.text(s, 0.5, 0.18, 11.2, 0.8, title, size=28, color="FFFFFF", bold=True, anchor=MSO_ANCHOR.MIDDLE)
        if os.path.exists(self.icon): s.shapes.add_picture(self.icon, PIn(12.25), PIn(0.17), height=PIn(0.8))
        if subtitle: self.text(s, 0.5, 1.25, 12.3, 0.5, subtitle, size=14, color=GREY)
        self.box(s, 0, 7.15, 13.333, 0.35, fill=LIGHT)
        self.text(s, 0.5, 7.15, 9, 0.35, f"LUSONG  |  {self.team}  |  Philippine Startup Challenge XI", size=10, color=GREY, anchor=MSO_ANCHOR.MIDDLE)
        self.text(s, 11.8, 7.15, 1.2, 0.35, str(self.n), size=10, color=GREY, align=PP_ALIGN.RIGHT, anchor=MSO_ANCHOR.MIDDLE)
        return s
    def table(self, s, x, y, w, rows, col_w=None, size=12, row_h=0.42):
        shp = s.shapes.add_table(len(rows), len(rows[0]), PIn(x), PIn(y), PIn(w), PIn(row_h * len(rows))); t = shp.table
        if col_w:
            for j, cw in enumerate(col_w): t.columns[j].width = PIn(cw)
        for i, row in enumerate(rows):
            for j, v in enumerate(row):
                c = t.cell(i, j); c.text = ""; p = c.text_frame.paragraphs[0]; r = p.add_run(); r.text = v; r.font.size = PPt(size); r.font.name = "Arial"
                c.margin_left = c.margin_right = PIn(0.08); c.margin_top = c.margin_bottom = PIn(0.04)
                if i == 0: c.fill.solid(); c.fill.fore_color.rgb = rgb(NAVY); r.font.bold = True; r.font.color.rgb = rgb("FFFFFF")
                else: c.fill.solid(); c.fill.fore_color.rgb = rgb("FFFFFF" if i % 2 else "F4F7FB"); r.font.color.rgb = rgb(INK)
        return t
    def stat(self, s, x, y, w, big, small, color=ORANGE):
        self.box(s, x, y, w, 1.6, fill="FFFFFF", line="D5DEEA")
        self.text(s, x + 0.1, y + 0.1, w - 0.2, 0.8, big, size=30, color=color, bold=True)
        self.text(s, x + 0.1, y + 0.85, w - 0.2, 0.7, small, size=11, color=GREY)

def build_deck(out_dir, team, school, members, mentor, email):
    d = Deck(team, school, members, mentor, email, out_dir); P = d.prs
    # 1 title
    s = P.slides.add_slide(P.slide_layouts[6]); d.n += 1; d.box(s, 0, 0, 13.333, 7.5, fill=NAVY)
    if os.path.exists(d.icon): s.shapes.add_picture(d.icon, PIn(1.2), PIn(1.5), height=PIn(4.5))
    d.text(s, 6.0, 1.6, 6.8, 1.2, "LUSONG", size=60, color="FFFFFF", bold=True)
    d.text(s, 6.0, 2.7, 6.8, 1.4, "Post-flood leptospirosis early warning and prophylaxis dispatch for every barangay", size=22, color="EAF2FF")
    d.text(s, 6.0, 4.1, 6.8, 0.6, "“Lumusong ka man, may bantay.”", size=18, color="FF8A3D", bold=True)
    d.text(s, 6.0, 5.0, 6.8, 1.6, [f"Team {team}  |  {school}", "Philippine Startup Challenge XI (2026)", "Initial Presentation Deck  |  Sector: Health  |  Technology: AI / SaaS"], size=14, color="C9D8F0")
    # 2 problem
    s = d.slide("Problem Description", "After every flood, a preventable epidemic follows two to four weeks later")
    d.stat(s, 0.5, 1.9, 3.0, "~12,000", "leptospirosis cases in 2026 by Oct 1, 46% above 2025 [6]")
    d.stat(s, 3.7, 1.9, 3.0, "4,859", "cases in just 13 weeks of the 2025 rainy season (Jun 8 to Sep 4) [5]")
    d.stat(s, 6.9, 1.9, 3.0, "3.6 M", "people in 9,643 barangays affected by Typhoon Uwan alone, Nov 2025 [2]", color=NAVY)
    d.stat(s, 10.1, 1.9, 2.75, "6%", "of health facilities had internet connectivity in 2024 [12]", color=NAVY)
    d.text(s, 0.5, 3.8, 12.3, 3.2, [
        "Leptospirosis is caught by wading through floodwater contaminated by rat urine. Severe cases need dialysis; deaths cluster among working-age adults who waded to work, clear drains, or rescue neighbors.",
        "Prevention exists and is cheap: DOH recommends doxycycline post-exposure prophylaxis for people who waded, most effective within 24 to 72 hours.",
        "The gap: nobody knows, in time, which households waded. Warnings are generic, prophylaxis arrives late, and surveillance counts cases only after they reach hospitals (CDC EID, Mar 2026; PMC 2025) [7][8].",
        "The frontline is thin and offline: 21.2 health workers per 10,000 vs WHO 44.5; 26,504 barangay health stations for 42,026 barangays [9][11].",
    ], size=14, bullet=True, spacing=8)
    # 3 why existing tools stop at the hazard
    s = d.slide("Why Existing Tools Do Not Solve It", "Hazard platforms are excellent; the post-flood health response layer is empty")
    d.table(s, 0.5, 1.9, 12.3, [
        ["Capability", "HazardHunterPH / GeoRiskPH", "PAGASA + Google flood forecasts", "DOH advisories", "BIMS / eLGU", "Telemedicine apps", "LUSONG"],
        ["Pre-event hazard maps", "Yes", "Yes", "No", "No", "No", "Uses them"],
        ["Finds exposed households after a flood", "No", "No", "No", "No", "No", "Yes"],
        ["Targets prophylaxis within 72 h", "No", "No", "Generic text", "No", "No", "Yes"],
        ["BHW house-to-house workflow, offline", "No", "No", "No", "Records only", "No", "Yes"],
        ["Symptom screening and referral", "No", "No", "No", "No", "Online, urban", "Yes, offline, rural"],
        ["Measures outcome per barangay", "No", "No", "No", "No", "No", "Yes"],
    ], col_w=[3.1, 1.9, 1.9, 1.4, 1.4, 1.4, 1.2], size=11, row_h=0.5)
    d.text(s, 0.5, 5.6, 12.3, 1.3, "DILG mandated the Barangay Information Management System (BIMS) for all barangays in Dec 2025, with 25,000+ barangays oriented by mid-2026 [15]. Household-level data now exists almost everywhere. Nobody has used it for post-flood health response yet.", size=14, color=NAVY)
    # 4 SDG
    s = d.slide("Sustainable Development Goal (SDG) Priority", "Primary: SDG 3 Good Health and Well-being")
    d.box(s, 0.5, 1.9, 6.0, 4.9, fill="4C9F38")
    d.text(s, 0.7, 2.0, 5.6, 4.7, [("SDG 3  Good Health and Well-being", {"bold": True, "size": 24, "color": "FFFFFF"}),
        ("Target 3.3: end the epidemics of water-borne and other communicable diseases", {"size": 15, "color": "FFFFFF"}),
        ("Target 3.d: strengthen capacity for early warning, risk reduction and management of national and global health risks", {"size": 15, "color": "FFFFFF"})], spacing=12)
    d.text(s, 6.9, 1.9, 6.0, 0.5, "Secondary SDGs", size=18, bold=True, color=NAVY)
    d.text(s, 6.9, 2.5, 6.0, 4.3, [
        "SDG 11.5: reduce deaths and the number of people affected by disasters, including water-related disasters",
        "SDG 13.1: strengthen resilience and adaptive capacity to climate-related hazards",
        "SDG 6.2: sanitation, through hotspot mapping of flood and waste exposure",
        "Alignment: Universal Health Care Act (interoperable records by 2028), RA 10121 (70% of LDRRMF for prevention and preparedness), ASEAN 2026 theme “Navigating Our Future, Together” (digital public goods, climate resilience)",
    ], size=14, bullet=True, spacing=10)
    # 5 target market
    s = d.slide("Target Market Segment", "Beneficiaries, users and the customers who pay")
    d.table(s, 0.5, 1.9, 12.3, [
        ["Segment", "Who", "Size", "Role"],
        ["Beneficiaries", "Residents of flood-prone barangays; working-age adults who wade most", "8.2 M people affected by floods and cyclones in 2025; 9,643 barangays hit by Uwan alone [2][3]", "Receive advisories and prophylaxis"],
        ["Primary users", "Barangay Health Workers, RHU nurses and physicians, MDRRMO officers", "42,026 barangays; 26,504 barangay health stations [11]", "Run the house-to-house response"],
        ["Paying customers", "City and municipal governments (LDRRMF prevention funds, health budget)", "About 1,640 LGUs; est. 600 municipalities + 60 cities with recurring floods", "Annual subscription"],
        ["Early adopters", "Marikina, Malabon, Navotas, Calumpit, Hagonoy, Cabanatuan, Dagupan, Naga, Ormoc, Cotabato City, Davao riverside districts", "Active MDRRMO, BIMS-oriented barangays, eLGU accounts (41 cities, 901 municipalities) [15]", "Pilot and reference customers"],
    ], col_w=[1.8, 4.3, 3.9, 2.3], size=11, row_h=0.8)
    # 6 solution
    s = d.slide("Solution", "LUSONG turns a flood event into a barangay-level health action list within hours")
    cols = [("LUSONG Command", "LGU and RHU web dashboard", ["Exposure engine fuses rainfall, flood depth maps, elevation, and BIMS household locations", "Ranks barangays by expected cases (AI model trained on past rainfall, flood extent and DOH weekly case data)", "Doxycycline need vs stock per barangay health station"]),
            ("LUSONG Bantay", "Offline-first BHW mobile app", ["Prioritized house-to-house list per purok", "3-question Filipino symptom screen (fever, calf pain, red eyes or jaundice)", "Prophylaxis log and one-tap RHU referral; syncs when signal returns"]),
            ("LUSONG Abiso", "Resident messaging", ["Targeted SMS in Filipino and regional languages within hours of a flood", "Residents reply 1 or 2 to self-report wading and symptoms", "Messenger and eGovPH channels next"])]
    for i, (t, sub, pts) in enumerate(cols):
        x = 0.5 + i * 4.15; d.box(s, x, 1.9, 3.95, 4.9, fill="FFFFFF", line="D5DEEA"); d.box(s, x, 1.9, 3.95, 0.12, fill=ORANGE)
        d.text(s, x + 0.15, 2.1, 3.65, 0.5, t, size=20, bold=True, color=NAVY); d.text(s, x + 0.15, 2.6, 3.65, 0.4, sub, size=12, color=GREY)
        d.text(s, x + 0.15, 3.1, 3.65, 3.6, pts, size=13, bullet=True, spacing=8)
    # 7 how it works
    s = d.slide("How It Works", "No hardware required; built on data the government already publishes")
    steps = [("1. Trigger", "PAGASA rainfall, flood forecast, or MDRRMO report"), ("2. Exposure", "Spatial join of flood layers with BIMS households (PostGIS)"), ("3. Risk ranking", "Gradient-boosted model on rainfall, flood extent, past cases"), ("4. Dispatch", "SMS to households; BHW lists pushed offline"), ("5. Response", "Prophylaxis logged, symptoms screened, referrals made"), ("6. Learn", "30-day case tracking; reports in DOH FHSIS format")]
    for i, (t, sub) in enumerate(steps):
        x = 0.5 + (i % 3) * 4.15; y = 1.9 + (i // 3) * 2.2
        d.box(s, x, y, 3.95, 1.9, fill=LIGHT if i % 2 == 0 else "FFFFFF", line="D5DEEA")
        d.text(s, x + 0.15, y + 0.15, 3.65, 0.5, t, size=18, bold=True, color=NAVY); d.text(s, x + 0.15, y + 0.7, 3.65, 1.1, sub, size=13)
    d.text(s, 0.5, 6.35, 12.3, 0.7, "Stack: Python (FastAPI, XGBoost, GeoPandas), PostgreSQL + PostGIS, React dashboard, progressive web app with IndexedDB offline sync, local SMS gateway, small language model for multilingual advisories with RHU sign-off.", size=12, color=GREY)
    # 8 features
    s = d.slide("Features", "Must-have features ship in the 8-week MVP; the rest follow after the first pilot")
    d.text(s, 0.5, 1.9, 6.0, 0.5, "MVP (must have)", size=18, bold=True, color=NAVY)
    d.text(s, 0.5, 2.4, 6.0, 4.5, ["Barangay map with household exposure scoring", "BIMS or census CSV import (no re-encoding)", "SMS blast with reply capture (1 = waded, 2 = symptoms)", "Offline BHW checklist and symptom screen", "Doxycycline stock vs need panel", "Referral queue for the RHU", "Exportable report in DOH FHSIS format"], size=14, bullet=True, spacing=8)
    d.text(s, 6.9, 1.9, 6.0, 0.5, "Next (after pilot)", size=18, bold=True, color=ORANGE)
    d.text(s, 6.9, 2.4, 6.0, 4.5, ["ML risk ranking trained on DOH weekly surveillance", "Messenger and eGovPH super app channels", "Rain gauge and community sensor ingestion", "Dengue, acute diarrhea and heat-illness modules", "Province and DOH regional dashboards", "GeoRiskPH API integration under MOU"], size=14, bullet=True, spacing=8)
    # 9 demo flow
    s = d.slide("The 75-Second Demo", "What judges will see on stage")
    demo = ["Pick a barangay; simulate 180 mm rainfall and 0.5 m flood depth", "Map turns red: 1,240 exposed households, 2,900 people", "Doxycycline need 2,900 doses vs 400 in stock; reorder suggested", "One tap: the judge's phone receives the Tagalog SMS", "BHW tablet, offline, shows the house-to-house list", "A symptom report escalates to the RHU referral queue"]
    for i, t in enumerate(demo):
        y = 1.9 + i * 0.82; d.box(s, 0.5, y, 0.6, 0.6, fill=ORANGE, shape=MSO_SHAPE.OVAL); d.text(s, 0.5, y, 0.6, 0.6, str(i + 1), size=18, bold=True, color="FFFFFF", align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE)
        d.text(s, 1.3, y, 7.0, 0.6, t, size=15, anchor=MSO_ANCHOR.MIDDLE)
    d.box(s, 8.7, 1.9, 4.15, 4.9, fill=NAVY)
    d.text(s, 8.9, 2.1, 3.8, 4.5, [("Sample SMS", {"bold": True, "size": 14, "color": "FF8A3D"}), ("LUSONG: Lumusong ka ba sa baha kahapon? Pumunta sa Barangay Health Station para sa libreng doxycycline ngayong araw. Kung may lagnat o sakit ng binti sa susunod na 2 linggo, magpakonsulta agad. Reply 1 kung lumusong, 2 kung may sintomas.", {"size": 14, "color": "FFFFFF"})], spacing=10)
    # 10 business model
    s = d.slide("Business Model", "LGUs pay from the prevention share of the Local DRRM Fund; prevention is cheaper than dialysis")
    d.table(s, 0.5, 1.9, 7.3, [["Revenue stream", "Price (PHP, to be validated)"], ["Municipal subscription (up to 30 barangays)", "60,000 per year + 1,500 per extra barangay"], ["Component city subscription", "150,000 to 300,000 per year"], ["Implementation and BHW training", "25,000 one-time per LGU"], ["SMS credits", "Cost plus 20%"], ["Province or DOH regional license", "Volume pricing"], ["Future: anonymized outbreak analytics", "Insurers, pharma distributors (DPA-compliant)"]], col_w=[3.9, 3.4], size=12, row_h=0.5)
    d.text(s, 8.1, 1.9, 4.8, 0.5, "Unit economics (estimate)", size=18, bold=True, color=NAVY)
    d.text(s, 8.1, 2.4, 4.8, 4.5, ["Cloud and SMS cost per municipality about PHP 12,000 per year", "Onboarding about 3 person-days", "Gross margin above 70% after year one", "10 LGUs cover the team's operating cost; 50 LGUs fund a full-time team of five", "Procurement through LDRRMF prevention allocation (RA 10121) is annual and renewable", "One avoided dialysis case costs more than a year of LUSONG"], size=13, bullet=True, spacing=8)
    # 11 market
    s = d.slide("Market Size and Demand", "Philippine LGU market first; exportable to ASEAN neighbors with endemic leptospirosis")
    d.stat(s, 0.5, 1.9, 4.0, "PHP 131 M / yr", "TAM: about 1,640 cities and municipalities x PHP 80,000 average (estimate); PHP 180 M with province and DOH licenses", color=NAVY)
    d.stat(s, 4.65, 1.9, 4.0, "PHP 48 M / yr", "SAM: est. 600 flood-prone municipalities x 60,000 + 60 cities x 200,000 (Uwan alone hit 725 LGUs [2])", color=NAVY)
    d.stat(s, 8.8, 1.9, 4.05, "PHP 8 M / yr", "SOM in 3 years: 100 LGUs plus SMS and training revenue")
    d.text(s, 0.5, 3.8, 12.3, 3.2, ["Leptospirosis cases up 46% year on year in 2026 [6]", "2026 GAA restored about PHP 129.8 B to PhilHealth; health sector budget PHP 448 B [16]", "DILG BIMS mandate (Dec 2025) supplies the household data input; UHC Act pushes LGUs toward interoperable digital health tools by 2028 [12][15]", "Philippine ASEAN chairmanship 2026 prioritizes digital public goods and climate resilience; leptospirosis is endemic in Indonesia, Thailand, Malaysia and Vietnam [17]", "No Philippine startup or agency product found that targets leptospirosis exposure and prophylaxis logistics"], size=13, bullet=True, spacing=8)
    # 12 progress / traction
    s = d.slide("Progress / Traction", "Status as of October 2026: idea stage with a validated problem and a build plan")
    d.text(s, 0.5, 1.9, 6.0, 0.5, "Done", size=18, bold=True, color=NAVY)
    d.text(s, 0.5, 2.4, 6.0, 4.5, ["Problem sized from DOH surveillance, NDRRMC and CDC/PMC literature (2025 to 2026)", "Competitive scan: hazard platforms, telemedicine, govtech; no post-flood health response product found", "Solution architecture, offline-first BHW workflow and multilingual SMS designed", "Exposure engine data sources identified: PAGASA, GeoRiskPH, NAMRIA, BIMS exports, DOH weekly tables"], size=13, bullet=True, spacing=8)
    d.text(s, 6.9, 1.9, 6.0, 0.5, "Next 8 weeks (commitments)", size=18, bold=True, color=ORANGE)
    d.text(s, 6.9, 2.4, 6.0, 4.5, ["15 interviews: 5 BHWs, 3 RHU clinicians, 3 MDRRMO officers, 2 barangay captains, 2 patient families", "Backtest the exposure engine on two 2024 to 2025 flood events against DOH weekly data", "Letter of Intent from one flood-prone LGU; 20 BHWs trained; 1,000 households enrolled", "Live pilot in one or two barangays; publish delivery time and reach numbers"], size=13, bullet=True, spacing=8)
    # 13 roadmap
    s = d.slide("Roadmap", "From MVP to ten paying LGUs in twelve months")
    d.table(s, 0.5, 1.9, 12.3, [["Milestone", "Target date", "Measure"], ["MVP with backtested exposure engine", "Regional pitch, Nov 2026", ">= 70% recall of barangays with later lepto clusters (estimate)"], ["First LGU LOI or MOA, 20 BHWs trained", "Dec 2026", "1,000 households enrolled in LUSONG Abiso"], ["Live through one rainy-season flood in 3 LGUs", "Jun 2027", ">= 60% of exposed households reached within 24 h; prophylaxis offer within 72 h"], ["Outcome evaluation vs matched control barangays", "Dec 2027", "Flood-to-first-dose under 48 h; fewer hospitalized cases"], ["10 paying LGUs, second disease module", "Dec 2027", "PHP 600,000 annual recurring revenue"]], col_w=[4.6, 2.6, 5.1], size=12, row_h=0.62)
    # 14 use of funds
    s = d.slide("Proposed Use of Funds / Asks", "PHP 480,000 for twelve months: MVP completion and pilots in three LGUs")
    d.table(s, 0.5, 1.9, 7.3, [["Item", "PHP"], ["Cloud, maps and model hosting", "60,000"], ["SMS and messaging credits (about 150,000 messages)", "90,000"], ["Field validation and pilot travel", "80,000"], ["BHW training materials and five shared devices", "60,000"], ["Multilingual content and clinical review", "40,000"], ["Legal, data privacy and registration", "30,000"], ["Student team stipends", "80,000"], ["Contingency", "40,000"], ["Total", "480,000"]], col_w=[5.3, 2.0], size=12, row_h=0.46)
    d.text(s, 8.1, 1.9, 4.8, 0.5, "Our asks", size=18, bold=True, color=NAVY)
    d.text(s, 8.1, 2.4, 4.8, 4.5, ["Pilot introductions to two flood-prone LGUs and their municipal health officers", "DICT Startup Grant Fund consideration (PHP 500,000 to 1 M) for year-one pilots", "DOH Epidemiology Bureau and GeoRiskPH data access under MOU", "Mentorship on LGU procurement and Data Privacy Act compliance", "Allocation: 44% product and infrastructure, 38% field validation and adoption, 18% compliance and contingency"], size=13, bullet=True, spacing=8)
    # 15 team
    s = d.slide("Team", f"{school}")
    roles = [(members[0] if len(members) > 0 else "Member 1", "CEO: customer validation, LGU partnerships, pitch, finance. info@astin.ph"), (members[1] if len(members) > 1 else "Member 2", "CTO: exposure and risk engine, backend, offline PWA. johnalvinbondoc@gmail.com"), (members[2] if len(members) > 2 else "Member 3", "CPO/CMO: BHW experience, multilingual content, training, brand. aurchierodriguez@gmail.com"), (mentor, "Mentor: technical review, DOH Region III and LGU introductions. ggsioson.au@phinmaed.com")]
    for i, (n, r) in enumerate(roles):
        x = 0.5 + (i % 2) * 6.2; y = 1.9 + (i // 2) * 2.5
        d.box(s, x, y, 6.0, 2.2, fill="FFFFFF", line="D5DEEA"); d.box(s, x, y, 0.15, 2.2, fill=ORANGE if i < 3 else NAVY)
        d.text(s, x + 0.35, y + 0.2, 5.5, 0.6, n, size=20, bold=True, color=NAVY); d.text(s, x + 0.35, y + 0.85, 5.5, 1.2, r, size=14)
    # 16 close
    s = P.slides.add_slide(P.slide_layouts[6]); d.n += 1; d.box(s, 0, 0, 13.333, 7.5, fill=NAVY)
    if os.path.exists(d.icon): s.shapes.add_picture(d.icon, PIn(1.2), PIn(1.5), height=PIn(4.5))
    d.text(s, 6.0, 2.0, 6.8, 1.0, "Lumusong ka man, may bantay.", size=34, color="FF8A3D", bold=True)
    d.text(s, 6.0, 3.2, 6.8, 1.6, "Every flood is followed by a preventable epidemic. LUSONG makes sure the PHP 10 antibiotic reaches the right house in time.", size=18, color="FFFFFF")
    d.text(s, 6.0, 5.0, 6.8, 1.5, [f"Team {team}  |  {school}", f"Contact: {email}", "Sources: see Concept Note, Section X and reference list"], size=14, color="C9D8F0")
    path = os.path.join(out_dir, f"{team}_PresentationDeck_PSC11.pptx"); P.save(path); return path

def to_pdf(path, out_dir):
    subprocess.run(["soffice", "--headless", "--convert-to", "pdf", "--outdir", out_dir, path], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--team", default="TeamLusong"); ap.add_argument("--school", default="PHINMA Araullo University, Cabanatuan City")
    ap.add_argument("--region", default="Region III (Central Luzon)"); ap.add_argument("--members", nargs="*", default=["Justine Tabunan (CEO)", "John Alvin D. Bondoc (CTO)", "Aurchie Keem N. Rodriguez (CPO/CMO)"])
    ap.add_argument("--mentor", default="Gabriel Nikiloz G. Sioson (CIT Faculty)"); ap.add_argument("--email", default="info@astin.ph / 0965 274 3311 (Justine Tabunan); ggsioson.au@phinmaed.com (mentor)")
    ap.add_argument("--out", default=os.path.join(ROOT, "submission"))
    a = ap.parse_args(); os.makedirs(a.out, exist_ok=True)
    build_logo(a.out, a.team); print("logo ok")
    cn = build_concept_note(a.out, a.team, a.school, a.region, a.members, a.mentor, a.email); to_pdf(cn, a.out); print("concept note ok")
    dk = build_deck(a.out, a.team, a.school, a.members, a.mentor, a.email); to_pdf(dk, a.out); print("deck ok")

if __name__ == "__main__":
    main()
