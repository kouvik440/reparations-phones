#!/usr/bin/env node

/**
 * uninstall-skill.js
 * Removes the dashboard-design skill from all AI coding agents.
 * Run manually: node uninstall-skill.js
 */

const fs = require('fs');
const path = require('path');
const os = require('os');

const SKILL_NAME = 'dashboard-design';

const AGENT_DIRS = [
  path.join(os.homedir(), '.claude',    'skills', SKILL_NAME),
  path.join(os.homedir(), '.cursor',    'skills', SKILL_NAME),
  path.join(os.homedir(), '.codex',     'skills', SKILL_NAME),
  path.join(os.homedir(), '.windsurf',  'skills', SKILL_NAME),
];

console.log('\n  Removing dashboard-design skill...\n');

for (const dir of AGENT_DIRS) {
  if (fs.existsSync(dir)) {
    fs.rmSync(dir, { recursive: true, force: true });
    console.log(`  ✓ Removed: ${dir}`);
  }
}

console.log('\n  Done.\n');
