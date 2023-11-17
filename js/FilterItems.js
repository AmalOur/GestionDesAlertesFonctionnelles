function filterCertificates() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const certificateItems = document.getElementsByClassName("certificate");

    Array.from(certificateItems).forEach((item) => {
        const certificateName = item.querySelector("h3").textContent.toLowerCase();
        if (certificateName.includes(input)) {
            item.style.display = "block";
        } else {
            item.style.display = "none";

        }
    });
}

document.getElementById("searchInput").addEventListener("input", filterCertificates);