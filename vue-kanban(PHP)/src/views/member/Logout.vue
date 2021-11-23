<script>
export default {
    created() {
        this.$logOut();
        this.$router.push({ path : "/login"});
    }
}
</script>