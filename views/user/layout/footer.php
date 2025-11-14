</body>
    <div id="sticky-ad-banner">
    <div class="ad-wrapper">
        <span id="close-ad-btn" onclick="closeStickyAd(event)">&times;</span>

        <a href="../../api/count_ad_click.php?ad=1" target="_blank">
            <img src="../../public/img/3bet_mb.gif" alt="Quảng cáo">
        </a>
    </div>
</div>

</html>
<script>
    function closeStickyAd(e) {
        // Ngăn trình duyệt click vào link quảng cáo
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Lấy phần tử banner
        var adBanner = document.getElementById('sticky-ad-banner');
        
        // Ẩn nó đi
        if (adBanner) {
            adBanner.style.display = 'none';
        }
    }
</script>