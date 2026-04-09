<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Blog;

class BlogSeeder extends Seeder
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
        
        $blogs = [
            [
                'title' => 'Getting Started with Academic Writing: A Beginner\'s Guide',
                'slug' => 'getting-started-with-academic-writing-beginners-guide',
                'excerpt' => 'Learn the fundamentals of academic writing, from structuring your arguments to proper citation practices.',
                'content' => '# Getting Started with Academic Writing: A Beginner\'s Guide

Academic writing can seem intimidating at first, but with the right approach, it becomes a valuable skill that will serve you throughout your academic career and beyond. In this comprehensive guide, we\'ll walk you through the essential elements of academic writing.

## Understanding Academic Writing

Academic writing is a formal style of writing used in academic settings. It\'s characterized by:

- **Clear and concise language**
- **Evidence-based arguments**
- **Logical structure**
- **Proper citation and referencing**

## The Writing Process

### 1. Pre-Writing Phase

Before you start writing, take time to:

- Understand your assignment requirements
- Research your topic thoroughly
- Create an outline
- Develop a thesis statement

### 2. Drafting

When drafting your paper:

- Start with a strong introduction
- Develop clear paragraphs with topic sentences
- Support your arguments with evidence
- Maintain a formal tone

### 3. Revision and Editing

The revision process is crucial:

- Check for clarity and coherence
- Ensure proper grammar and spelling
- Verify your citations
- Get feedback from peers or mentors

## Common Mistakes to Avoid

1. **Plagiarism** - Always cite your sources properly
2. **Informal language** - Maintain academic tone
3. **Weak arguments** - Support claims with evidence
4. **Poor structure** - Follow logical organization

## Tips for Success

- Start early and give yourself plenty of time
- Break large tasks into smaller, manageable steps
- Use writing resources and tools available
- Practice regularly to improve your skills

## Conclusion

Academic writing is a skill that improves with practice. By following these guidelines and dedicating time to develop your writing abilities, you\'ll become a more confident and effective academic writer.

Remember, even experienced writers continue to learn and improve. Be patient with yourself and celebrate your progress along the way!',
                'category' => 'tips',
                'tags' => ['academic writing', 'research', 'tips', 'beginners'],
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'The Impact of AI on Academic Research',
                'slug' => 'impact-of-ai-on-academic-research',
                'excerpt' => 'Explore how artificial intelligence is transforming the landscape of academic research and what it means for researchers.',
                'content' => '# The Impact of AI on Academic Research

Artificial intelligence is revolutionizing academic research in unprecedented ways. From data analysis to literature reviews, AI tools are becoming indispensable for modern researchers.

## AI-Powered Literature Reviews

One of the most time-consuming aspects of research is conducting comprehensive literature reviews. AI tools can now:

- Analyze thousands of papers in minutes
- Identify key themes and gaps in research
- Suggest relevant studies you might have missed
- Generate comprehensive summaries

## Data Analysis and Pattern Recognition

AI excels at:

- Processing large datasets quickly
- Identifying patterns humans might miss
- Predictive modeling
- Statistical analysis automation

## Writing and Editing Assistance

AI writing tools help researchers:

- Improve clarity and coherence
- Check for grammar and style
- Suggest better phrasing
- Ensure proper formatting

## Ethical Considerations

While AI offers tremendous benefits, researchers must:

- Maintain academic integrity
- Verify AI-generated content
- Disclose AI usage when appropriate
- Avoid over-reliance on automated tools

## The Future of AI in Research

The integration of AI in academic research is just beginning. We can expect:

- More sophisticated research tools
- Enhanced collaboration platforms
- Improved peer review processes
- Greater accessibility to research

## Conclusion

AI is not replacing researchers but augmenting their capabilities. By embracing these tools responsibly, academics can focus more on creative thinking and innovation while AI handles repetitive tasks.

The key is to find the right balance between human expertise and artificial intelligence.',
                'category' => 'ai-tools',
                'tags' => ['AI', 'research', 'technology', 'future'],
                'status' => 'published',
                'is_featured' => false,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Building Your Academic Network: Strategies for Success',
                'slug' => 'building-academic-network-strategies-success',
                'excerpt' => 'Learn how to build meaningful professional relationships that can advance your academic career.',
                'content' => '# Building Your Academic Network: Strategies for Success

In academia, who you know can be just as important as what you know. Building a strong professional network opens doors to collaborations, opportunities, and career advancement.

## Why Networking Matters in Academia

Academic networking helps you:

- Find research collaborators
- Discover new opportunities
- Get mentorship and guidance
- Stay updated with field developments
- Increase visibility of your work

## Where to Network

### Conferences and Events

- Present your research
- Attend social events
- Join poster sessions
- Participate in workshops

### Online Platforms

- Academic social networks
- Professional associations
- Research collaboration platforms
- Online forums and groups

### Institutional Connections

- Department colleagues
- Cross-department collaborations
- Alumni networks
- Industry partnerships

## Networking Strategies

### Be Genuine and Authentic

- Show genuine interest in others\' work
- Share your own research openly
- Offer help before asking for favors
- Follow up on conversations

### Quality Over Quantity

- Focus on meaningful connections
- Nurture existing relationships
- Be selective about events to attend
- Prioritize relevant networks

### Give Before You Take

- Share resources and information
- Introduce people to each other
- Offer feedback on work
- Volunteer for committees

## Maintaining Your Network

### Stay in Touch

- Send occasional updates
- Congratulate on achievements
- Share relevant papers or news
- Remember important details

### Provide Value

- Share opportunities
- Offer expertise
- Make introductions
- Give constructive feedback

## Common Networking Mistakes

1. **Being too transactional** - Focus on building genuine relationships
2. **Not following up** - Stay in touch with new contacts
3. **Only networking when needed** - Build relationships continuously
4. **Ignoring online presence** - Maintain professional online profiles

## Measuring Success

Track your networking progress by:

- Number of meaningful connections
- Collaboration opportunities
- Invitations to speak or present
- Mentoring relationships
- Career advancements

## Conclusion

Building an academic network is a long-term investment in your career. Start early, be consistent, and focus on building genuine relationships that benefit both parties.

Remember, networking is about creating a community of scholars who support and learn from each other.',
                'category' => 'career',
                'tags' => ['networking', 'career', 'professional development', 'collaboration'],
                'status' => 'published',
                'is_featured' => false,
                'published_at' => now()->subDays(1),
            ],
        ];
        
        foreach ($blogs as $blogData) {
            Blog::create(array_merge($blogData, [
                'user_id' => $user->id,
                'views' => rand(50, 500),
                'likes_count' => rand(5, 50),
                'comments_count' => rand(2, 20),
            ]));
        }
        
        $this->command->info('Sample blog posts created successfully!');
    }
}
