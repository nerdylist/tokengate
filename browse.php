<?php
require_once 'config.php';

// Sample human profile data
$profiles = [
    [
        'id' => 'KEA-2847',
        'name' => 'Alexander Liteplo',
        'verified' => true,
        'rating' => 4.9,
        'location' => 'Nairobi, Kenya',
        'remote' => true,
        'bio' => 'Full-stack developer specializing in modern web applications. 8+ years experience with PHP, JavaScript, and cloud infrastructure.',
        'skills' => ['php', 'javascript', 'react', 'aws', 'docker'],
        'price' => 69
    ],
    [
        'id' => 'CAN-9201',
        'name' => 'Patricia Tani',
        'verified' => true,
        'rating' => 5.0,
        'location' => 'Vancouver, BC',
        'remote' => true,
        'bio' => 'Senior AI/ML engineer with expertise in neural networks and large-scale data processing. Published researcher and tech lead.',
        'skills' => ['python', 'tensorflow', 'machine learning', 'ai', 'data science'],
        'price' => 420
    ],
    [
        'id' => 'BGD-3456',
        'name' => 'Amir Kallas',
        'verified' => true,
        'rating' => 4.7,
        'location' => 'Dacca, BD',
        'remote' => true,
        'bio' => 'Creative UI/UX designer focused on mobile-first experiences. Passionate about accessibility and user-centered design.',
        'skills' => ['ui design', 'figma', 'mobile design', 'prototyping'],
        'price' => 50
    ],
    [
        'id' => 'IRN-8834',
        'name' => 'M. Ameen',
        'verified' => false,
        'rating' => 4.5,
        'location' => 'Iran',
        'remote' => true,
        'bio' => 'Backend developer with strong database optimization skills. Experience building high-performance APIs at scale.',
        'skills' => ['node.js', 'postgresql', 'redis', 'api design'],
        'price' => 50
    ],
    [
        'id' => 'SLE-7722',
        'name' => 'David Montgomery',
        'verified' => true,
        'rating' => 4.8,
        'location' => 'Freetown, SL',
        'remote' => true,
        'bio' => 'DevOps engineer specializing in CI/CD pipelines and infrastructure automation. Kubernetes certified.',
        'skills' => ['kubernetes', 'terraform', 'ci/cd', 'monitoring'],
        'price' => 75
    ],
    [
        'id' => 'IRN-9912',
        'name' => 'Xander Liteplo',
        'verified' => true,
        'rating' => 4.6,
        'location' => 'Iran',
        'remote' => true,
        'bio' => 'Mobile app developer building cross-platform solutions. Strong focus on performance and user experience.',
        'skills' => ['react native', 'ios', 'android', 'mobile'],
        'price' => 50
    ],
    [
        'id' => 'FRA-4401',
        'name' => 'Pieri Shapiro',
        'verified' => true,
        'rating' => 4.9,
        'location' => 'Louisiana, Outre, France',
        'remote' => true,
        'bio' => 'Cybersecurity specialist with penetration testing and security audit expertise. OSCP and CISSP certified.',
        'skills' => ['security', 'penetration testing', 'compliance', 'auditing'],
        'price' => 200
    ],
    [
        'id' => 'SWE-1188',
        'name' => 'EU_Node_Gothenburg',
        'verified' => true,
        'rating' => 4.3,
        'location' => 'Gothenburg, Sweden',
        'remote' => true,
        'bio' => 'Distributed computing node operator. Providing reliable infrastructure for decentralized applications 24/7.',
        'skills' => ['infrastructure', 'networking', 'blockchain', 'hosting'],
        'price' => 20
    ],
    [
        'id' => 'MEX-5567',
        'name' => 'Ricardo CC',
        'verified' => false,
        'rating' => 4.4,
        'location' => 'San Luis Potosi, San Luis Potosi, Mexico',
        'remote' => true,
        'bio' => 'WordPress and e-commerce specialist. Built 100+ sites for clients worldwide with focus on conversions.',
        'skills' => ['wordpress', 'woocommerce', 'seo', 'php'],
        'price' => 50
    ],
    [
        'id' => 'JPN-6623',
        'name' => 'Yuki Nakamura',
        'verified' => true,
        'rating' => 5.0,
        'location' => 'Tokyo, Japan',
        'remote' => true,
        'bio' => 'Game developer and 3D artist. Specialized in Unity and Unreal Engine with VR/AR experience.',
        'skills' => ['unity', 'unreal engine', '3d modeling', 'vr', 'ar'],
        'price' => 150
    ],
    [
        'id' => 'NGA-4429',
        'name' => 'Chioma Okafor',
        'verified' => true,
        'rating' => 4.8,
        'location' => 'Lagos, Nigeria',
        'remote' => true,
        'bio' => 'Content strategist and technical writer. Creating clear documentation and engaging content for tech companies.',
        'skills' => ['technical writing', 'content strategy', 'documentation', 'copywriting'],
        'price' => 45
    ],
    [
        'id' => 'AUS-3398',
        'name' => 'Sophie Chen',
        'verified' => true,
        'rating' => 4.9,
        'location' => 'Sydney, Australia',
        'remote' => true,
        'bio' => 'Data analyst transforming complex datasets into actionable insights. Expert in visualization and storytelling.',
        'skills' => ['data analysis', 'python', 'tableau', 'sql', 'statistics'],
        'price' => 95
    ],
    [
        'id' => 'BRA-8871',
        'name' => 'Lucas Silva',
        'verified' => false,
        'rating' => 4.2,
        'location' => 'São Paulo, Brazil',
        'remote' => true,
        'bio' => 'QA engineer with automation expertise. Building robust test suites to ensure product quality and reliability.',
        'skills' => ['qa automation', 'selenium', 'testing', 'ci/cd'],
        'price' => 40
    ],
    [
        'id' => 'GER-7754',
        'name' => 'Elena Schmidt',
        'verified' => true,
        'rating' => 4.9,
        'location' => 'Berlin, Germany',
        'remote' => true,
        'bio' => 'Product manager with technical background. Bridging the gap between business goals and engineering teams.',
        'skills' => ['product management', 'agile', 'roadmapping', 'analytics'],
        'price' => 180
    ],
    [
        'id' => 'IND-2203',
        'name' => 'Raj Patel',
        'verified' => true,
        'rating' => 4.7,
        'location' => 'Mumbai, India',
        'remote' => true,
        'bio' => 'Blockchain developer building decentralized applications. Smart contract auditing and Web3 integration specialist.',
        'skills' => ['solidity', 'ethereum', 'web3', 'smart contracts'],
        'price' => 65
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Humans - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="browse.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-title-wrapper">
                    <h1 class="page-title">browse humans</h1>
                </div>
                <p class="page-subtitle">find freelance workers for your agent</p>
            </section>

            <section class="filters-tabs">
                <div class="tabs">
                    <button class="tab active" data-filter="all">all</button>
                    <button class="tab" data-filter="tech">tech</button>
                    <button class="tab" data-filter="woman">woman</button>
                    <button class="tab" data-filter="other">other</button>
                </div>
            </section>

            <section class="profiles-section">
                <div class="profiles-grid">
                    <?php foreach ($profiles as $profile): ?>
                        <?php include 'partials/profile-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
