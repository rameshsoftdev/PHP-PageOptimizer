<?php
require_once 'vendor/autoload.php'; // Include the Composer autoloader
use Intervention\Image\ImageManager;
use MatthiasMullie\Minify;
use Intervention\Image\ImageManagerStatic as Image;
class PageOptimizer
{
    private $imageManager;

    public function __construct()
    {
        // Initialize the Intervention ImageManager
        $this->imageManager = new ImageManager();
    }
    public function convertToWebP($imagePath)
    {
        // Check if GD extension with WebP support is available
        if (extension_loaded('gd') && function_exists('imagewebp')) {
            // Get the file extension
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

            // Check if the image is in a supported format (JPEG or PNG)
            if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') {
                // Load the original image based on the file type
                if ($extension === 'jpg' || $extension === 'jpeg') {
                    $image = imagecreatefromjpeg($imagePath);
                } elseif ($extension === 'png') {
                    $image = imagecreatefrompng($imagePath);
                }

                if ($image !== false) {
                    // Define the path for the WebP image
                    $webpPath = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME) . '.webp';

                    // Convert and save the image in WebP format
                    imagewebp($image, $webpPath);

                    // Free up memory
                    imagedestroy($image);

                    // Return the path to the generated WebP image
                    return $webpPath;
                } else {
                    // Failed to load the image
                    return false;
                }
            } else {
                // Unsupported image format
                return false;
            }
        } else {
            // GD extension with WebP support is not available
            return false;
        }
    }

    public function optimizeImages($imagePath)
    {
        try {
            // Open the image using Intervention Image
            $image = $this->imageManager->make($imagePath);

            // Optimize the image (reduce quality, strip metadata, etc.)
            $image->optimize();

            // Save the optimized image (overwrite the original)
            $image->save($imagePath);

            // Return the path to the optimized image
            return $imagePath;
        } catch (Exception $e) {
            // Handle exceptions if any
            return false;
        }
    }

    public function lazyLoadImages()
    {
        // Implement lazy loading for images and background images using JavaScript and jQuery
        echo '<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>';
        echo '<script>
                $(document).ready(function() {
                    // Select all images and elements with background images on the page
                    var allLazyElements = document.querySelectorAll("img[data-src], [data-bg-src]");
    
                    // Loop through each element
                    allLazyElements.forEach(function(element) {
                        // Check if the element has a data-src or data-bg-src attribute
                        var dataSrc = element.getAttribute("data-src");
                        var dataBgSrc = element.getAttribute("data-bg-src");
                        var isAlreadyLoaded = element.classList.contains("lazy-loaded");
    
                        if (dataSrc && !isAlreadyLoaded) {
                            // If data-src is present and element is not already loaded, set it as the src attribute
                            element.setAttribute("src", "' . addslashes(dataSrc) . '");
    
                            // Remove the data-src attribute after the element has loaded
                            element.onload = function() {
                                element.removeAttribute("data-src");
                                element.classList.add("lazy-loaded");
                            };
                        } else if (dataBgSrc && !isAlreadyLoaded) {
                            // If data-bg-src is present and element is not already loaded, set it as the background image
                            element.style.backgroundImage = "url(" + "' . addslashes(dataBgSrc) . '" + ")";
    
                            // Remove the data-bg-src attribute after the element has loaded
                            var image = new Image();
                            image.src = "' . addslashes(dataBgSrc) . '";
                            image.onload = function() {
                                element.removeAttribute("data-bg-src");
                                element.classList.add("lazy-loaded");
                            };
                        } else {
                            // If data-src or data-bg-src is not present or element is already loaded, consider it as already loaded
                            element.classList.add("lazy-loaded");
                        }
                    });
    
                    // Implement additional code for dynamically loading elements as the user scrolls
                    // Using Intersection Observer API
                    var options = {
                        root: null,
                        rootMargin: "0px",
                        threshold: 0.5
                    };
    
                    var intersectionObserver = new IntersectionObserver(function(entries, observer) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                // Load the element when it comes into view
                                var elementToLoad = entry.target;
                                var src = elementToLoad.getAttribute("data-src");
                                var bgSrc = elementToLoad.getAttribute("data-bg-src");
    
                                if (src) {
                                    // Load image for <img> element
                                    elementToLoad.src = "' . addslashes(src) . '";
                                    elementToLoad.removeAttribute("data-src");
                                } else if (bgSrc) {
                                    // Load background image for <div> element
                                    elementToLoad.style.backgroundImage = "url(" + "' . addslashes(bgSrc) . '" + ")";
                                    elementToLoad.removeAttribute("data-bg-src");
                                }
    
                                elementToLoad.classList.add("lazy-loaded");
                                observer.unobserve(elementToLoad);
                            }
                        });
                    }, options);
    
                    // Observe each lazy element on the page
                    allLazyElements.forEach(function(element) {
                        if (!element.classList.contains("lazy-loaded")) {
                            intersectionObserver.observe(element);
                        }
                    });
                });
              </script>';
    }

    public function preloadCriticalCSS($cssPath)
    {
        // Implement preloading of critical CSS
        echo '<link rel="preload" as="style" href="' . $cssPath . '" />';
    }

    public function minifyCSS($cssContent)
    {
        // Implement CSS minification using the minify library
        $minifier = new Minify\CSS($cssContent);

        // Minify the CSS content
        $minifiedCSS = $minifier->minify();

        return $minifiedCSS;
    }

    public function minifyJavaScript($jsContent)
    {
        // Implement JavaScript minification using the minify library
        $minifier = new Minify\JS($jsContent);

        // Minify the JavaScript content
        $minifiedJS = $minifier->minify();

        return $minifiedJS;
    }

    public function deferJavaScript($jsPath)
    {
        // Implement deferring of non-critical JavaScript
        echo '<script defer src="' . $jsPath . '"></script>';
    }

    public function serveScaledImages($imagePath, $width, $height)
    {
        // Check if Intervention Image is available
        if (class_exists('Intervention\Image\Image')) {
            // Open the image using Intervention Image
            $image = Image::make($imagePath);

            // Resize the image to the specified dimensions
            $image->fit($width, $height);

            // Set the content type header for the response
            header('Content-Type: ' . $image->mime());

            // Output the resized image
            echo $image->encode();

            // Destroy the Intervention Image instance to free up memory
            $image->destroy();
        } else {
            // Intervention Image is not available
            echo 'Intervention Image library is not installed.';
        }
    }

    public function avoidCSSExclusions($cssFilePath)
    {
        // Read the existing CSS content
        $existingCSS = file_get_contents($cssFilePath);

        // Modify the CSS content to include best practices to avoid CSS exclusions
        $updatedCSS = $existingCSS . '
            /* Additional CSS rules to avoid exclusions */
            .avoid-absolute {
                position: relative; /* Use relative positioning instead */
            }

            .avoid-float {
                display: inline-block; /* Use inline-block or other layout techniques instead */
            }
        ';

        // Write the updated CSS content back to the file
        file_put_contents($cssFilePath, $updatedCSS);
    }

    public function googleFontsOptimization($fontUrl)
    {
        // Implement Google Fonts optimization using font-display: swap and preconnect
        echo '<link rel="stylesheet" href="' . $fontUrl . '" media="print" onload="this.media=\'all\'">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>';
    }
}

// Example usage:
$pageOptimizer = new PageOptimizer();
$pageOptimizer->convertToWebP('/path/to/image.jpg');
$pageOptimizer->optimizeImages('/path/to/image.jpg');
$pageOptimizer->lazyLoadImages();
$pageOptimizer->preloadCriticalCSS('/path/to/styles.css');
$minifiedCSS = $pageOptimizer->minifyCSS('/* Your CSS code */');
$minifiedJS = $pageOptimizer->minifyJavaScript('/* Your JS code */');
$pageOptimizer->deferJavaScript('/path/to/script.js');
$pageOptimizer->serveScaledImages('/path/to/image.jpg', 300, 200);
$pageOptimizer->avoidCSSExclusions('/path/to/style-sheet.css');
$pageOptimizer->googleFontsOptimization('https://fonts.googleapis.com/css?family=Open+Sans');




// Example usage:
$pageOptimizer = new PageOptimizer();
$webpImagePath = $pageOptimizer->convertToWebP('/path/to/image.jpg');

if ($webpImagePath !== false) {
    echo 'WebP image generated: ' . $webpImagePath;
} else {
    echo 'WebP conversion failed.';
}
