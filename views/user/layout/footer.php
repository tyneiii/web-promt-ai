</body>
    <div id="sticky-ad-banner" style="text-align: center; padding: 15px 0;">
        <span id="close-ad-btn" onclick="closeStickyAd()">&times;</span>
        <a href="../../api/count_ad_click.php?ad=1" target="_blank"> 
            <img src="../../public/img/ads.jpg" alt="Quảng cáo">
        </a>
    </div>
</html>
<script>
    function closeStickyAd() {
        // Lấy phần tử banner
        var adBanner = document.getElementById('sticky-ad-banner');
        
        // Ẩn nó đi
        if (adBanner) {
            adBanner.style.display = 'none';
        }
    }
</script>