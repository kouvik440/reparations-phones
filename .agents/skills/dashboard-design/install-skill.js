#!/usr/bin/env node

/**
 * install-skill.js
 * Installs the dashboard-design skill to all detected AI coding agents.
 * Runs automatically after `npm install` via the postinstall script.
 *
 * Supported agents:
 *   - Claude Code  → ~/.claude/skills/dashboard-design/
 *   - Cursor       → ~/.cursor/skills/dashboard-design/
 *   - Codex        → ~/.codex/skills/dashboard-design/
 *   - Windsurf     → ~/.windsurf/skills/dashboard-design/
 */

const fs = require('fs');
const path = require('path');
const os = require('os');

const SKILL_NAME = 'dashboard-design';
const PKG_ROOT = path.resolve(__dirname);

const AGENTS = [
  { name: 'Claude Code', dir: path.join(os.homedir(), '.claude', 'skills', SKILL_NAME) },
  { name: 'Cursor',      dir: path.join(os.homedir(), '.cursor', 'skills', SKILL_NAME) },
  { name: 'Codex',       dir: path.join(os.homedir(), '.codex', 'skills', SKILL_NAME) },
  { name: 'Windsurf',    dir: path.join(os.homedir(), '.windsurf', 'skills', SKILL_NAME) },
];

// Files to install: root SKILL.md + all category files
const SKILL_FILES = [
  { src: path.join(PKG_ROOT, 'SKILL.md'),                                            dest: 'SKILL.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'visual-hierarchy.md'),           dest: 'visual-hierarchy.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'kpi-cards.md'),                  dest: 'kpi-cards.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'chart-selection.md'),            dest: 'chart-selection.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'colour-system.md'),              dest: 'colour-system.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'typography.md'),                 dest: 'typography.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'layout.md'),                     dest: 'layout.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'component-states.md'),           dest: 'component-states.md' },
  { src: path.join(PKG_ROOT, 'skills', SKILL_NAME, 'tables.md'),                     dest: 'tables.md' },
];

function detectAgents() {
  return AGENTS.filter(agent => {
    // Check if the parent skills folder already exists (agent is installed)
    const skillsParent = path.dirname(agent.dir);
    return fs.existsSync(skillsParent) || fs.existsSync(path.dirname(skillsParent));
  });
}

function installToAgent(agent) {
  try {
    fs.mkdirSync(agent.dir, { recursive: true });
    let installed = 0;
    for (const file of SKILL_FILES) {
      if (fs.existsSync(file.src)) {
        fs.copyFileSync(file.src, path.join(agent.dir, file.dest));
        installed++;
      }
    }
    console.log(`  ✓ ${agent.name} — installed ${installed} files to ${agent.dir}`);
    return true;
  } catch (err) {
    console.log(`  ✗ ${agent.name} — failed: ${err.message}`);
    return false;
  }
}

function main() {
  console.log('\n┌─────────────────────────────────────────────┐');
  console.log('│  dashboard-design skill  ·  installing...  │');
  console.log('└─────────────────────────────────────────────┘\n');

  const detected = detectAgents();

  if (detected.length === 0) {
    // Install to Claude Code by default even if not yet detected
    console.log('  No agents detected — installing to Claude Code by default.\n');
    const claudeDir = path.join(os.homedir(), '.claude', 'skills', SKILL_NAME);
    installToAgent({ name: 'Claude Code', dir: claudeDir });
  } else {
    console.log(`  Found ${detected.length} agent(s):\n`);
    for (const agent of detected) {
      installToAgent(agent);
    }
  }

  console.log('\n  Use the skill:\n');
  console.log('  "Using the dashboard-design skill, review this KPI card for hierarchy issues."');
  console.log('  "Following dashboard-design chart-selection, what chart for this data?"');
  console.log('  "Check this table against the dashboard-design tables checklist."\n');
}

main();
