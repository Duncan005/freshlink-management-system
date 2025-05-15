<?php
// This file provides an inline SVG hero image that can be directly included
// This ensures we have a hero image even if external files aren't loading

header('Content-Type: image/svg+xml');
echo <<<SVG
<svg width="1920" height="1080" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="#4CAF50"/>
  <text x="50%" y="50%" font-family="Arial" font-size="48" fill="white" text-anchor="middle">FreshLink Farm Hero</text>
</svg>
SVG;
?>