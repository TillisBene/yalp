<?php 

namespace algorithm;

use Phpml\Math\Distance\Euclidean;
use Phpml\Math\Distance\Manhattan;
use Phpml\Classification\KNearestNeighbors;

/**
 * Class InterestMapManager
 * Manages user interests and provides content relevance calculations
 */
class InterestMapManager
{
    /** @var array Stores interest categories and their feature vectors */
    private $interestMap = [];
    
    /** @var KNearestNeighbors Machine learning classifier for interest categorization */
    private $classifier;
    
    /** @var float Minimum score required for a post to be considered relevant */
    private const RELEVANCE_THRESHOLD = 0.7;

    /**
     * Initialize the manager with K-Nearest Neighbors classifier
     * Using 5 neighbors and Euclidean distance metric
     */
    public function __construct()
    {
        $this->classifier = new KNearestNeighbors(5, new Euclidean());
    }

    /**
     * Add a new interest category with its feature vector to the map
     * @param string $category The interest category name
     * @param array $features Vector of numerical features representing the interest
     */
    public function addInterest(string $category, array $features)
    {
        $this->interestMap[] = [
            'category' => $category,
            'features' => $features
        ];
    }

    /**
     * Train the classifier with all stored interest data
     */
    public function train()
    {
        $samples = [];
        $labels = [];
        
        foreach ($this->interestMap as $interest) {
            $samples[] = $interest['features'];
            $labels[] = $interest['category'];
        }

        $this->classifier->train($samples, $labels);
    }

    /**
     * Predict the category for a new feature vector
     * @param array $features Input feature vector
     * @return string Predicted category
     */
    public function predict(array $features)
    {
        return $this->classifier->predict($features);
    }

    /**
     * Filter and sort posts based on user interests and location
     * @param array $userInterestVector User's interest feature vector
     * @param array $posts Array of posts to filter
     * @param array $userLocation User's geographic coordinates [lat, lon]
     * @return array Sorted array of relevant posts with scores
     */
    public function getRelevantPosts(array $userInterestVector, array $posts, array $userLocation)
    {
        $relevantPosts = [];
        
        foreach ($posts as $post) {
            $similarity = $this->calculateSimilarity($userInterestVector, $post['interestVector']);
            $distance = $this->calculateGeographicDistance($userLocation, $post['location']);
            
            $score = $this->calculateRelevanceScore($similarity, $distance);
            
            if ($score >= self::RELEVANCE_THRESHOLD) {
                $relevantPosts[] = [
                    'post' => $post,
                    'score' => $score
                ];
            }
        }

        // Sort posts by relevance score in descending order
        usort($relevantPosts, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $relevantPosts;
    }

    /**
     * Calculate cosine similarity between two feature vectors
     * @param array $vector1 First feature vector
     * @param array $vector2 Second feature vector
     * @return float Similarity score between 0 and 1
     */
    private function calculateSimilarity(array $vector1, array $vector2)
    {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        foreach ($vector1 as $i => $value) {
            $dotProduct += $value * $vector2[$i];
            $norm1 += $value * $value;
            $norm2 += $vector2[$i] * $vector2[$i];
        }

        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }

    /**
     * Calculate geographic distance between two points
     * @param array $loc1 First location coordinates [lat, lon]
     * @param array $loc2 Second location coordinates [lat, lon]
     * @return float Euclidean distance between points
     */
    private function calculateGeographicDistance(array $loc1, array $loc2)
    {
        return sqrt(pow($loc1[0] - $loc2[0], 2) + pow($loc1[1] - $loc2[1], 2));
    }

    /**
     * Calculate final relevance score combining similarity and distance
     * @param float $similarity Content similarity score
     * @param float $distance Geographic distance
     * @return float Combined relevance score between 0 and 1
     */
    private function calculateRelevanceScore($similarity, $distance)
    {
        // Convert distance to proximity score (closer = higher score)
        $normalizedDistance = max(0, 1 - ($distance / 100));
        
        // Weighted combination: 70% content similarity, 30% geographic proximity
        return ($similarity * 0.7) + ($normalizedDistance * 0.3);
    }
}