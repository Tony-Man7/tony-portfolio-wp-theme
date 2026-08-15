fetch('http://tony-portfolio-wp.test/wp-json/tdp/v1/ask', {
	method: 'POST',
	headers: { 'Content-Type': 'application/json' },
	body: JSON.stringify({ question: 'May experience ba siyang gumawa ng ecommerce website?' })
})
	.then(res => res.json())
	.then(data => console.log(data));

//////////////////////////////////////////

const questions = [
	'May experience ba siyang gumawa ng ecommerce website?',
	'Ano stack niya sa Laravel projects?',
	'May ginawa ba siyang super mahabang project na maraming detalye...' // e.g. designed to test MAX_TOKENS
];

function testSequentially(i = 0) {
	if (i >= questions.length) return;
	fetch('http://tony-portfolio-wp.test/wp-json/tdp/v1/ask', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ question: questions[i] })
	})
		.then(res => res.json())
		.then(data => {
			console.log(`Q${i + 1}:`, data);
			setTimeout(() => testSequentially(i + 1), 6000); // 6s gap
		});
}

testSequentially();

//////////////////////////////////////////

fetch('http://tony-portfolio-wp.test/wp-json/tdp/v1/job-match', {
	method: 'POST',
	headers: { 'Content-Type': 'application/json' },
	body: JSON.stringify({
		job_description: 'We are looking for a Full-stack Developer with experience in Laravel, Vue.js, and WordPress/WooCommerce. Must have built e-commerce sites before.'
	})
})
	.then(res => res.json())
	.then(data => console.log(data));