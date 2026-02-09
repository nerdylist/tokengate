<section class="filters-section">
    <form class="filters-form" method="get" action="">
        <div class="filter-group">
            <select name="category" id="category" class="filter-select">
                <option value="">all categories</option>
                <option value="documentation">documentation</option>
                <option value="design">design</option>
                <option value="research">research</option>
                <option value="development">development</option>
                <option value="writing">writing</option>
                <option value="marketing">marketing</option>
            </select>
        </div>
        <div class="filter-group">
            <input type="text" name="skill" id="skill" class="filter-input" placeholder="photography ...">
        </div>
        <div class="filter-group filter-group-price">
            <input type="number" name="min_price" id="min_price" class="filter-input" placeholder="0" min="0">
        </div>
        <div class="filter-group filter-group-price">
            <input type="number" name="max_price" id="max_price" class="filter-input" placeholder="1000" min="0">
        </div>
        <div class="filter-group">
            <button type="submit" class="btn-search">search</button>
        </div>
    </form>
</section>
