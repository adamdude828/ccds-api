#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const readline = require('readline');

// Create readline interface for user input
const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

// Paths for template and output files
const templatePath = path.join(__dirname, '..', '.npmrc.template');
const outputPath = path.join(__dirname, '..', '.npmrc');

/**
 * Base64 encode a string
 * @param {string} str - String to encode
 * @returns {string} Base64 encoded string
 */
function base64Encode(str) {
  return Buffer.from(str).toString('base64');
}

/**
 * Prompt user for Azure PAT
 * @returns {Promise<string>} User input
 */
function promptForPAT() {
  return new Promise((resolve) => {
    rl.question('Enter your Azure Personal Access Token (PAT): ', (answer) => {
      resolve(answer);
    });
  });
}

/**
 * Main function to generate .npmrc file
 */
async function generateNpmrc() {
  try {
    console.log('Setting up .npmrc file with Azure PAT...');
    
    // Check if template exists
    if (!fs.existsSync(templatePath)) {
      throw new Error(`.npmrc.template file not found at ${templatePath}`);
    }
    
    // Read template file
    const templateContent = fs.readFileSync(templatePath, 'utf8');
    
    // Get PAT from user
    const pat = await promptForPAT();
    
    if (!pat) {
      throw new Error('PAT cannot be empty');
    }
    
    // Encode PAT
    const encodedPat = base64Encode(pat);
    
    // Replace placeholder with encoded PAT
    const outputContent = templateContent.replace(/{{AZURE_PAT_BASE64}}/g, encodedPat);
    
    // Write to .npmrc file
    fs.writeFileSync(outputPath, outputContent);
    
    console.log(`\n✅ Successfully created .npmrc file at ${outputPath}`);
    console.log('You can now use npm commands with Azure DevOps repositories');
  } catch (error) {
    console.error(`\n❌ Error: ${error.message}`);
    process.exit(1);
  } finally {
    rl.close();
  }
}

// Run the script
generateNpmrc(); 