<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Journal;

class SampleJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }
        
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Machine Learning Applications in Healthcare: A Comprehensive Review',
            'slug' => 'machine-learning-applications-in-healthcare-comprehensive-review',
            'abstract' => 'This paper provides a comprehensive review of machine learning applications in healthcare, covering recent advances, challenges, and future directions. We analyze various ML techniques applied to medical diagnosis, treatment planning, and drug discovery, highlighting both successes and limitations.',
            'introduction' => '<h3>Background</h3><p>The integration of machine learning into healthcare represents one of the most significant technological advances in modern medicine. Over the past decade, we have witnessed unprecedented growth in AI-powered medical applications, from diagnostic imaging to personalized treatment recommendations.</p><h3>Objectives</h3><p>This review aims to: (1) Survey the current landscape of ML applications in healthcare, (2) Identify key challenges and limitations, (3) Discuss ethical considerations, and (4) Provide insights into future research directions.</p>',
            'materials_methods' => '<h3>Search Strategy</h3><p>We conducted a systematic review of literature published between 2015 and 2024, searching major databases including PubMed, IEEE Xplore, and arXiv. Our search terms included "machine learning", "healthcare", "medical AI", and related keywords.</p><h3>Inclusion Criteria</h3><p>Studies were included if they: (1) Focused on ML applications in healthcare, (2) Provided empirical results, (3) Were peer-reviewed, and (4) Published in English.</p>',
            'results_discussion' => '<h3>Key Findings</h3><p>Our analysis revealed several key trends: (1) Deep learning models show superior performance in medical imaging tasks, (2) Natural language processing is revolutionizing clinical documentation, (3) Predictive analytics are improving patient outcomes, and (4) Federated learning is emerging as a solution for data privacy concerns.</p><h3>Challenges</h3><p>Despite promising results, significant challenges remain, including data quality issues, model interpretability, regulatory hurdles, and the need for robust validation in clinical settings.</p>',
            'conclusion' => '<p>Machine learning has demonstrated remarkable potential to transform healthcare delivery and patient outcomes. However, realizing this potential requires addressing technical, ethical, and regulatory challenges. Future research should focus on developing more interpretable models, ensuring equitable access, and establishing robust validation frameworks. The collaboration between data scientists, clinicians, and policymakers will be crucial for responsible AI adoption in healthcare.</p>',
            'area_of_study' => 'Computer Science',
            'status' => 'published',
            'posted_for_review_at' => now()->subDays(30),
            'published_at' => now()->subDays(15),
            'views' => 245,
            'average_rating' => 4.2,
        ]);
        
        $this->command->info('Sample journal created successfully! ID: ' . $journal->id);
    }
}
