<style>
    .spin-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,0.10);
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
    }
    .spin-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.15);
    }
    .spin-card .spin-viewer-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16/10;
        background: #111;
        overflow: hidden;
        cursor: grab;
    }
    .spin-card .spin-viewer-wrap:active {
        cursor: grabbing;
    }
    .spin-card .spin-viewer-wrap canvas {
        width: 100%;
        height: 100%;
        display: block;
    }
    .spin-card .spin-viewer-wrap .spin-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 8px 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.45));
        pointer-events: none;
    }
    .spin-card .spin-overlay span {
        color: rgba(255,255,255,0.7);
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-family: system-ui, -apple-system, sans-serif;
    }
    .spin-card .spin-overlay .spin-arrows {
        color: rgba(255,255,255,0.55);
        font-size: 14px;
        letter-spacing: 2px;
    }
    .spin-card .spin-img-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16/10;
        background-size: cover;
        background-position: center;
        background-color: #eee;
    }
    .spin-card .card-info {
        padding: 18px 20px 20px;
    }
    .spin-card .card-info .price-row {
        display: flex;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 8px;
    }
    .spin-card .card-info .price-main {
        font-size: 18px;
        font-weight: 700;
        color: #222;
    }
    .spin-card .card-info .price-sub {
        font-size: 13px;
        color: #999;
    }
    .spin-card .card-info .prop-features {
        list-style: none;
        padding: 0;
        margin: 0 0 8px;
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }
    .spin-card .card-info .prop-features li {
        font-size: 13px;
        color: #666;
    }
    .spin-card .card-info .prop-features li i,
    .spin-card .card-info .prop-features li span[class^="flaticon"] {
        margin-right: 4px;
        color: #c2ac1f;
    }
    .spin-card .card-info h5 {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 4px;
    }
    .spin-card .card-info h5 a {
        color: #333;
        text-decoration: none;
    }
    .spin-card .card-info h5 a:hover {
        color: #c2ac1f;
    }
    .spin-card .card-info .location-text {
        font-size: 12px;
        color: #999;
        margin-bottom: 12px;
        display: block;
    }
    .spin-card .btn-tour {
        display: block;
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        background: #c2ac1f;
        color: #fff;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
        border: none;
    }
    .spin-card .btn-tour:hover {
        background: #a89618;
        color: #fff;
        text-decoration: none;
    }
</style>
